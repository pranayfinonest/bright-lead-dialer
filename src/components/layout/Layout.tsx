import { ReactNode } from "react";
import Sidebar from "./Sidebar";
import Header from "./Header";
import MobileNavigation from "@/components/mobile/MobileNavigation";
import MobileHeader from "@/components/mobile/MobileHeader";
import { useLocation } from "react-router-dom";

interface LayoutProps {
  children: ReactNode;
}

const getPageInfo = (pathname: string) => {
  switch (pathname) {
    case "/": return { title: "Dashboard", subtitle: "Welcome back!" };
    case "/leads": return { title: "Leads", subtitle: "Manage your prospects" };
    case "/dialer": return { title: "Auto Dialer", subtitle: "Smart calling system" };
    case "/campaigns": return { title: "Campaigns", subtitle: "Manage campaigns" };
    case "/messages": return { title: "Messages", subtitle: "SMS & WhatsApp" };
    case "/schedule": return { title: "Schedule", subtitle: "Your calendar" };
    case "/analytics": return { title: "Analytics", subtitle: "Performance insights" };
    case "/settings": return { title: "Settings", subtitle: "Configure your account" };
    default: return { title: "TeleCRM", subtitle: "" };
  }
};

export default function Layout({ children }: LayoutProps) {
  const location = useLocation();
  const pageInfo = getPageInfo(location.pathname);

  return (
    <div className="min-h-screen bg-background">
      {/* Desktop Layout */}
      <div className="hidden md:flex h-screen">
        <Sidebar />
        <div className="flex-1 flex flex-col overflow-hidden">
          <Header />
          <main className="flex-1 overflow-auto p-6">
            {children}
          </main>
        </div>
      </div>

      {/* Mobile Layout */}
      <div className="md:hidden min-h-screen">
        <MobileHeader title={pageInfo.title} subtitle={pageInfo.subtitle} />
        <main className="pb-20 px-4 pt-4">
          {children}
        </main>
        <MobileNavigation />
      </div>
    </div>
  );
}