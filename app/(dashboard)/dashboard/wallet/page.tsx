import { WalletHeader } from "@/components/wallet/wallet-header";
import { StatsCards } from "@/components/dashboard/stats-cards";
import { MyWallet } from "@/components/wallet/my-wallet";
import { TransactionsHistory } from "@/components/wallet/transactions-history";

export default function WalletPage() {
  return (
    <div className="space-y-6">
      <WalletHeader title="Wallet" />

      {/* Stats Cards */}
      <StatsCards totalBalance={1000} submittedBids={100} approvedLoans={900} />

      {/* My Wallet Section */}
      <MyWallet />

      {/* Transactions History */}
      <TransactionsHistory transactions={[]} />
    </div>
  );
}
