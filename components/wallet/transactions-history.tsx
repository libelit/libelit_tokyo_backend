"use client";

import { ArrowUpDown } from "lucide-react";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";

export interface Transaction {
  id: string;
  type: string;
  date: string;
  time: string;
  project: string;
  noOfTokens: number;
  value: number;
}

interface TransactionsHistoryProps {
  transactions: Transaction[];
}

function SortableHeader({ children }: { children: React.ReactNode }) {
  return (
    <button className="flex items-center gap-1 hover:text-gray-900 transition-colors">
      {children}
      <ArrowUpDown className="h-3 w-3" />
    </button>
  );
}

export function TransactionsHistory({ transactions }: TransactionsHistoryProps) {
  return (
    <div className="space-y-4">
      <h2 className="text-lg font-semibold">Transactions history</h2>
      <div className="overflow-hidden rounded-xl">
        <Table>
          <TableHeader>
            <TableRow className="border-b border-gray-200 hover:bg-transparent bg-white">
              <TableHead className="py-4 font-medium text-gray-600">
                <SortableHeader>Type</SortableHeader>
              </TableHead>
              <TableHead className="py-4 font-medium text-gray-600">
                <SortableHeader>Date</SortableHeader>
              </TableHead>
              <TableHead className="py-4 font-medium text-gray-600">
                <SortableHeader>Time</SortableHeader>
              </TableHead>
              <TableHead className="py-4 font-medium text-gray-600">
                <SortableHeader>Project</SortableHeader>
              </TableHead>
              <TableHead className="py-4 text-center font-medium text-gray-600">
                <SortableHeader>No. of tokens</SortableHeader>
              </TableHead>
              <TableHead className="py-4 text-center font-medium text-gray-600">
                <SortableHeader>Value</SortableHeader>
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {transactions.length === 0 ? (
              <>
                <TableRow className="border-b border-gray-200 hover:bg-transparent">
                  <TableCell colSpan={6} className="py-8 text-center">
                    <span className="text-base text-gray-600">
                      You don&apos;t have any transactions yet.
                    </span>
                  </TableCell>
                </TableRow>
                <TableRow className="hover:bg-transparent">
                  <TableCell colSpan={6} className="h-16"></TableCell>
                </TableRow>
              </>
            ) : (
              transactions.map((transaction) => (
                <TableRow key={transaction.id} className="border-b border-gray-200">
                  <TableCell className="py-4 font-medium">{transaction.type}</TableCell>
                  <TableCell className="py-4">{transaction.date}</TableCell>
                  <TableCell className="py-4">{transaction.time}</TableCell>
                  <TableCell className="py-4">{transaction.project}</TableCell>
                  <TableCell className="py-4 text-center">{transaction.noOfTokens}</TableCell>
                  <TableCell className="py-4 text-center">
                    ${transaction.value.toLocaleString()}
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </div>
    </div>
  );
}
