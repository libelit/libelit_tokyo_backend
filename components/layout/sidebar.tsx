"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { Menu, User, LogOut } from "lucide-react";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Sheet, SheetContent, SheetTrigger } from "@/components/ui/sheet";
import { Separator } from "@/components/ui/separator";
import { Logo } from "@/components/shared/logo";
import { navigationConfig } from "@/config/navigation";
import { useState } from "react";

function NavGroup({
  items,
  onItemClick
}: {
  items: typeof navigationConfig[0]["items"];
  onItemClick?: () => void;
}) {
  const pathname = usePathname();

  return (
    <div className="flex flex-col gap-1">
      {items.map((item) => {
        const isActive = pathname === item.href;
        return (
          <Link
            key={item.href}
            href={item.href}
            onClick={onItemClick}
            className={cn(
              "flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors",
              isActive
                ? "bg-white text-black shadow-sm"
                : "text-gray-700 hover:bg-white/50 hover:text-gray-900"
            )}
          >
            <item.icon className="h-5 w-5" />
            {item.title}
          </Link>
        );
      })}
    </div>
  );
}

function UserProfile() {
  return (
    <div className="flex items-center gap-3 px-3 py-3">
      <div className="flex h-10 w-10 items-center justify-center rounded-full bg-[#909FAD]">
        <User className="h-5 w-5 text-white" />
      </div>
      <div className="flex flex-col flex-1">
        <span className="text-sm font-medium text-gray-900">Superfund</span>
        <span className="text-xs">Investor</span>
      </div>
      <button className="p-2 hover:bg-white/50 rounded-lg transition-colors cursor-pointer">
        <LogOut className="h-5 w-5 text-black" />
      </button>
    </div>
  );
}

export function Sidebar() {
  return (
    <aside
      className="hidden lg:flex sticky top-0 h-screen w-64 flex-col border-l border-[#E0E0E0] p-4"
      style={{ background: "linear-gradient(to top, #B9C2CA, #E0E0E0, #E6EAED)" }}
    >
      <div className="mb-8">
        <Logo showText />
      </div>

      <nav>
        <NavGroup items={navigationConfig[0].items} />
      </nav>

      <div className="mt-auto">
        <Separator className="my-4 bg-[#B9C2CA]" />
        <NavGroup items={navigationConfig[1].items} />
        <div className="mt-4">
          <UserProfile />
        </div>
      </div>
    </aside>
  );
}

export function MobileSidebar() {
  const [open, setOpen] = useState(false);

  return (
    <Sheet open={open} onOpenChange={setOpen}>
      <SheetTrigger asChild>
        <Button variant="ghost" size="icon" className="lg:hidden">
          <Menu className="h-6 w-6" />
          <span className="sr-only">Toggle menu</span>
        </Button>
      </SheetTrigger>
      <SheetContent
        side="left"
        className="w-64 p-4 border-r border-gray-200 flex flex-col"
        style={{ background: "linear-gradient(to top, #B9C2CA, #E0E0E0, #E6EAED)" }}
      >
        <div className="mb-8">
          <Logo showText />
        </div>

        <nav>
          <NavGroup items={navigationConfig[0].items} onItemClick={() => setOpen(false)} />
        </nav>

        <div className="mt-auto">
          <Separator className="my-4 bg-[#B9C2CA]" />
          <NavGroup items={navigationConfig[1].items} onItemClick={() => setOpen(false)} />
          <div className="mt-4">
            <UserProfile />
          </div>
        </div>
      </SheetContent>
    </Sheet>
  );
}
