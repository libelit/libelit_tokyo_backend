interface AuthLayoutProps {
  children: React.ReactNode;
}

export default function AuthRouteLayout({ children }: AuthLayoutProps) {
  return <>{children}</>;
}
