<?php

namespace App\Services;

use App\Enums\XrplTxStatusEnum;
use App\Enums\XrplTxTypeEnum;
use App\Models\BlockchainAuditLog;
use App\Models\XrplTransaction;
use Hardcastle\XRPL_PHP\Client\JsonRpcClient;
use Hardcastle\XRPL_PHP\Wallet\Wallet;
use Illuminate\Support\Facades\Log;
use Exception;

class XrplService
{
    private string $rpcUrl;
    private ?string $serverSeed;
    private bool $isTestnet;
    private ?JsonRpcClient $client = null;
    private ?Wallet $wallet = null;

    public function __construct()
    {
        $this->isTestnet = config('xrpl.testnet', true);
        $this->rpcUrl = config('xrpl.rpc_url', 'https://s.altnet.rippletest.net:51234');
        $this->serverSeed = config('xrpl.server_seed');
    }

    /**
     * Check if XRPL is properly configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->serverSeed) && $this->serverSeed !== 'sYourTestnetSeedHere';
    }

    /**
     * Get the JSON-RPC client.
     */
    private function getClient(): JsonRpcClient
    {
        if (!$this->client) {
            $this->client = new JsonRpcClient($this->rpcUrl);
        }
        return $this->client;
    }

    /**
     * Get the wallet.
     */
    private function getWallet(): Wallet
    {
        if (!$this->wallet) {
            if (!$this->isConfigured()) {
                throw new Exception('XRPL server seed not configured');
            }
            $this->wallet = Wallet::fromSeed($this->serverSeed);
        }
        return $this->wallet;
    }

    /**
     * Submit an audit hash to the XRPL ledger.
     */
    public function submitAuditHash(BlockchainAuditLog $auditLog): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'XRPL credentials not configured',
            ];
        }

        try {
            $client = $this->getClient();
            $wallet = $this->getWallet();
            $walletAddress = $wallet->getAddress();

            // Build transaction with memos
            $tx = [
                'TransactionType' => 'AccountSet',
                'Account' => $walletAddress,
                'Memos' => [
                    [
                        'Memo' => [
                            'MemoType' => bin2hex('audit_hash'),
                            'MemoData' => bin2hex($auditLog->data_hash),
                        ]
                    ],
                    [
                        'Memo' => [
                            'MemoType' => bin2hex('event_type'),
                            'MemoData' => bin2hex($auditLog->event_type->value),
                        ]
                    ],
                    [
                        'Memo' => [
                            'MemoType' => bin2hex('entity_id'),
                            'MemoData' => bin2hex((string) $auditLog->auditable_id),
                        ]
                    ],
                    [
                        'Memo' => [
                            'MemoType' => bin2hex('app'),
                            'MemoData' => bin2hex('libelit_tokyo'),
                        ]
                    ]
                ]
            ];

            // Autofill, sign, and submit
            $autofilledTx = $client->autofill($tx);
            $signedTx = $wallet->sign($autofilledTx);
            $response = $client->submitAndWait($signedTx['tx_blob']);

            // Parse response
            $responseData = $response->getResult();
            $txHash = $responseData['hash'] ?? null;
            $status = $responseData['meta']['TransactionResult'] ?? 'unknown';
            $ledgerIndex = $responseData['ledger_index'] ?? null;
            $sequence = $responseData['Sequence'] ?? null;

            if ($status !== 'tesSUCCESS') {
                throw new Exception("Transaction failed with status: {$status}");
            }

            // Create XrplTransaction record
            $xrplTransaction = XrplTransaction::create([
                'tx_hash' => $txHash,
                'tx_type' => XrplTxTypeEnum::ACCOUNT_SET,
                'from_address' => $walletAddress,
                'to_address' => $walletAddress,
                'amount' => 0,
                'currency' => 'XRP',
                'fee' => 0.000012,
                'sequence' => $sequence,
                'ledger_index' => $ledgerIndex,
                'status' => XrplTxStatusEnum::VALIDATED,
                'related_type' => $auditLog->auditable_type,
                'related_id' => $auditLog->auditable_id,
                'raw_response' => $responseData,
                'validated_at' => now(),
            ]);

            Log::info('XRPL audit hash submitted', [
                'audit_log_id' => $auditLog->id,
                'event_type' => $auditLog->event_type->value,
                'tx_hash' => $txHash,
                'ledger_index' => $ledgerIndex,
            ]);

            return [
                'success' => true,
                'tx_hash' => $txHash,
                'ledger_index' => $ledgerIndex,
                'xrpl_transaction_id' => $xrplTransaction->id,
            ];

        } catch (Exception $e) {
            Log::error('XRPL audit submission failed', [
                'audit_log_id' => $auditLog->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify if a transaction has been validated on the ledger.
     */
    public function verifyTransaction(string $txHash): array
    {
        try {
            $client = $this->getClient();

            // Use the tx method to get transaction details
            $response = $client->request('tx', [
                'transaction' => $txHash,
                'binary' => false,
            ]);

            $result = $response->getResult();

            if (isset($result['validated']) && $result['validated'] === true) {
                return [
                    'success' => true,
                    'validated' => true,
                    'ledger_index' => $result['ledger_index'] ?? null,
                    'raw_response' => $result,
                ];
            }

            return [
                'success' => true,
                'validated' => false,
                'raw_response' => $result,
            ];

        } catch (Exception $e) {
            Log::error('XRPL transaction verification failed', [
                'tx_hash' => $txHash,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get the explorer URL for a transaction.
     */
    public function getExplorerUrl(string $txHash): string
    {
        $baseUrl = $this->isTestnet
            ? 'https://testnet.xrpl.org/transactions/'
            : 'https://livenet.xrpl.org/transactions/';

        return $baseUrl . $txHash;
    }
}
