"use client";

import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { MobileSidebar } from "./sidebar";
import {Bell} from "lucide-react";

interface DashboardHeaderProps {
  title: string;
  subtitle?: string;
}

export function DashboardHeader({ title, subtitle }: DashboardHeaderProps) {
  return (
    <header className="flex items-center justify-between">
      <div className="flex items-center gap-4">
        <MobileSidebar />
        <div>
          <h1 className="text-2xl font-bold">{title}</h1>
          {subtitle && (
            <p className="text-sm text-foreground">{subtitle}</p>
          )}
        </div>
      </div>

      <button className="p-2 hover:bg-gray-100 rounded-full transition-colors">
        <Bell className="h-5 w-5 text-gray-600" />
      </button>
    </header>
  );
}
