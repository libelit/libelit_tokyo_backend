import Image from "next/image";

interface AuthLayoutProps {
  children: React.ReactNode;
  imageSrc: string;
}

export function AuthLayout({ children, imageSrc }: AuthLayoutProps) {
  return (
    <div className="min-h-screen grid lg:grid-cols-2">
      {/* Left side - Form */}
      <div className="flex flex-col justify-center p-6">
        {children}
      </div>

      {/* Right side - Decorative image (sticky) */}
      <div className="hidden lg:block sticky top-0 h-screen">
        <Image src={imageSrc} alt="" fill className="object-cover" priority />
      </div>
    </div>
  );
}
