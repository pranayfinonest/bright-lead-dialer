import { Phone, MessageSquare, UserCheck, Calendar, Clock } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";

interface ActivityItem {
  id: string;
  type: "call" | "sms" | "lead" | "meeting";
  title: string;
  description: string;
  time: string;
  status?: "success" | "failed" | "pending";
}

const activities: ActivityItem[] = [
  {
    id: "1",
    type: "call",
    title: "Call with John Smith",
    description: "Discussed loan requirements - Follow up needed",
    time: "2 min ago",
    status: "success"
  },
  {
    id: "2",
    type: "lead",
    title: "New lead added",
    description: "Sarah Johnson - Home loan inquiry",
    time: "15 min ago",
    status: "pending"
  },
  {
    id: "3",
    type: "sms",
    title: "SMS sent to 25 contacts",
    description: "Monthly loan offer campaign",
    time: "1 hour ago",
    status: "success"
  },
  {
    id: "4",
    type: "meeting",
    title: "Scheduled callback",
    description: "Mike Wilson - 3:00 PM today",
    time: "2 hours ago",
    status: "pending"
  },
  {
    id: "5",
    type: "call",
    title: "Call attempt failed",
    description: "Lisa Brown - Number not reachable",
    time: "3 hours ago",
    status: "failed"
  }
];

const activityIcons = {
  call: Phone,
  sms: MessageSquare,
  lead: UserCheck,
  meeting: Calendar
};

const statusColors = {
  success: "bg-success/10 text-success border-success/20",
  failed: "bg-destructive/10 text-destructive border-destructive/20",
  pending: "bg-warning/10 text-warning border-warning/20"
};

export default function RecentActivity() {
  return (
    <Card className="bg-gradient-card shadow-soft">
      <CardHeader className="pb-3">
        <CardTitle className="text-lg font-semibold flex items-center justify-between">
          <div className="flex items-center space-x-2">
            <Clock className="h-5 w-5 text-primary" />
            <span>Recent Activity</span>
          </div>
          <Badge variant="outline" className="text-xs">
            Live
          </Badge>
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          {activities.map((activity) => {
            const Icon = activityIcons[activity.type];
            return (
              <div
                key={activity.id}
                className="flex items-start space-x-3 p-3 rounded-lg hover:bg-muted/30 transition-colors"
              >
                <div className="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                  <Icon className="h-4 w-4 text-primary" />
                </div>
                <div className="flex-1 min-w-0">
                  <div className="flex items-center justify-between">
                    <h4 className="text-sm font-medium text-foreground truncate">
                      {activity.title}
                    </h4>
                    {activity.status && (
                      <Badge 
                        variant="outline" 
                        className={cn("text-xs ml-2", statusColors[activity.status])}
                      >
                        {activity.status}
                      </Badge>
                    )}
                  </div>
                  <p className="text-xs text-muted-foreground mt-1">
                    {activity.description}
                  </p>
                  <p className="text-xs text-muted-foreground mt-1 font-medium">
                    {activity.time}
                  </p>
                </div>
              </div>
            );
          })}
        </div>
      </CardContent>
    </Card>
  );
}