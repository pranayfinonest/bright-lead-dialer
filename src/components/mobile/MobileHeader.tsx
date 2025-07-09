import { Menu, Bell, Search } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Sheet,
  SheetContent,
  SheetTrigger,
} from "@/components/ui/sheet";
import { NavLink } from "react-router-dom";

const allNavigationItems = [
  { label: "Home", path: "/" },
  { label: "Leads", path: "/leads" },
  { label: "Dialer", path: "/dialer" },
  { label: "Campaigns", path: "/campaigns" },
  { label: "Messages", path: "/messages" },
  { label: "Schedule", path: "/schedule" },
  { label: "Analytics", path: "/analytics" },
  { label: "Settings", path: "/settings" }
];

interface MobileHeaderProps {
  title: string;
  subtitle?: string;
}

export default function MobileHeader({ title, subtitle }: MobileHeaderProps) {
  return (
    <header className="sticky top-0 z-40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-b md:hidden">
      <div className="flex items-center justify-between h-14 px-4">
        <div className="flex items-center gap-3">
          <Sheet>
            <SheetTrigger asChild>
              <Button variant="ghost" size="icon" className="h-9 w-9">
                <Menu className="h-5 w-5" />
              </Button>
            </SheetTrigger>
            <SheetContent side="left" className="w-64">
              <div className="mt-6">
                <div className="flex items-center gap-3 mb-4">
                  <img 
                    src="/lovable-uploads/616a0c1b-31a6-4d33-8b87-22ae8b11270e.png" 
                    alt="FINONEST Logo" 
                    className="h-8 w-8 rounded-full"
                  />
                  <div>
                    <h2 className="text-lg font-semibold">FINONEST</h2>
                    <p className="text-xs text-muted-foreground">trust comes first</p>
                  </div>
                </div>
                <nav className="space-y-2">
                  {allNavigationItems.map((item) => (
                    <NavLink
                      key={item.path}
                      to={item.path}
                      className={({ isActive }) =>
                        `block px-3 py-2 rounded-md text-sm transition-colors ${
                          isActive
                            ? "bg-primary text-primary-foreground"
                            : "hover:bg-muted"
                        }`
                      }
                    >
                      {item.label}
                    </NavLink>
                  ))}
                </nav>
              </div>
            </SheetContent>
          </Sheet>
          
          <div>
            <h1 className="font-semibold text-base">{title}</h1>
            {subtitle && (
              <p className="text-xs text-muted-foreground">{subtitle}</p>
            )}
          </div>
        </div>

        <div className="flex items-center gap-2">
          <Button variant="ghost" size="icon" className="h-9 w-9">
            <Search className="h-4 w-4" />
          </Button>
          <Button variant="ghost" size="icon" className="h-9 w-9 relative">
            <Bell className="h-4 w-4" />
            <Badge className="absolute -top-1 -right-1 h-5 w-5 flex items-center justify-center p-0 text-[10px]">
              3
            </Badge>
          </Button>
        </div>
      </div>
    </header>
  );
}