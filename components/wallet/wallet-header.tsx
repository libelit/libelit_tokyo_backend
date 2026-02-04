"use client";

import { Bell } from "lucide-react";
import { MobileSidebar } from "@/components/layout/sidebar";

interface WalletHeaderProps {
  title: string;
}

export function WalletHeader({ title }: WalletHeaderProps) {
  return (
    <header className="flex items-center justify-between">
      <div className="flex items-center gap-4">
        <MobileSidebar />
        <h1 className="text-2xl font-bold">{title}</h1>
      </div>
      <button className="p-2 hover:bg-gray-100 rounded-full transition-colors">
        <Bell className="h-5 w-5 text-gray-600" />
      </button>
    </header>
  );
}
