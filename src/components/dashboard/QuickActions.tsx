import { Phone, UserPlus, MessageSquare, Target, Calendar, Import } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export default function QuickActions() {
  const actions = [
    {
      icon: Phone,
      label: "Start Dialing",
      description: "Begin your calling session",
      variant: "call" as const,
      onClick: () => console.log("Start dialing")
    },
    {
      icon: UserPlus,
      label: "Add Lead",
      description: "Add new prospect",
      variant: "default" as const,
      onClick: () => console.log("Add lead")
    },
    {
      icon: MessageSquare,
      label: "Send SMS",
      description: "Quick message blast",
      variant: "secondary" as const,
      onClick: () => console.log("Send SMS")
    },
    {
      icon: Target,
      label: "New Campaign",
      description: "Create calling campaign",
      variant: "outline" as const,
      onClick: () => console.log("New campaign")
    },
    {
      icon: Calendar,
      label: "Schedule",
      description: "View today's calls",
      variant: "ghost" as const,
      onClick: () => console.log("Schedule")
    },
    {
      icon: Import,
      label: "Import Leads",
      description: "Upload CSV file",
      variant: "outline" as const,
      onClick: () => console.log("Import leads")
    }
  ];

  return (
    <Card className="bg-gradient-card shadow-soft">
      <CardHeader className="pb-3">
        <CardTitle className="text-lg font-semibold flex items-center space-x-2">
          <div className="w-5 h-5 bg-gradient-primary rounded"></div>
          <span>Quick Actions</span>
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-2 lg:grid-cols-3 gap-3">
          {actions.map((action, index) => (
            <Button
              key={index}
              variant={action.variant}
              className="h-auto p-4 flex-col items-start space-y-2 text-left"
              onClick={action.onClick}
            >
              <action.icon className="h-5 w-5 mb-1" />
              <div>
                <div className="font-medium text-sm">{action.label}</div>
                <div className="text-xs opacity-70">{action.description}</div>
              </div>
            </Button>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}