"use client";

import { Button } from "@/components/ui/button";
import {Separator} from "@/components/ui/separator";

interface MyWalletProps {
  hasWallet?: boolean;
  onCreateWallet?: () => void;
  onConnectWallet?: () => void;
}

export function MyWallet({
  hasWallet = false,
  onCreateWallet,
  onConnectWallet
}: MyWalletProps) {
  if (hasWallet) {
    // TODO: Implement wallet connected state
    return null;
  }

  return (
    <div className="space-y-4">
      <h2 className="text-lg font-semibold">My Wallet</h2>
      <Separator className="my-4 bg-[#B9C2CA]" />
      <div className="flex flex-col items-center justify-center">
        <p className="text-foreground py-10">You don't have any wallet yet</p>
        <Separator className="my-4 bg-[#B9C2CA]" />
        <div className="flex flex-col sm:flex-row gap-3 mt-4">
          <Button
            onClick={onCreateWallet}
            className="bg-black hover:bg-gray-800 text-white px-6 rounded-full cursor-pointer"
          >
            Create wallet
          </Button>
          <Button
            variant="outline"
            onClick={onConnectWallet}
            className="border-gray-300 text-gray-700 px-6 rounded-full cursor-pointer"
          >
            Connect wallet
          </Button>
        </div>
      </div>
    </div>
  );
}
