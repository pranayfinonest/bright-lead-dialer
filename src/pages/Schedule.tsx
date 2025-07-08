import { Calendar, Clock, Plus, Phone, Video, MessageSquare } from "lucide-react";
import Layout from "@/components/layout/Layout";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";

const todaySchedule = [
  {
    id: 1,
    time: "09:00 AM",
    type: "Call",
    contact: "Rajesh Kumar",
    phone: "+91 98765 43210",
    purpose: "Home Loan Follow-up",
    status: "Scheduled"
  },
  {
    id: 2,
    time: "10:30 AM",
    type: "Call",
    contact: "Priya Sharma",
    phone: "+91 87654 32109",
    purpose: "Document Collection",
    status: "Completed"
  },
  {
    id: 3,
    time: "02:00 PM",
    type: "Meeting",
    contact: "Amit Patel",
    phone: "+91 76543 21098",
    purpose: "Loan Approval Discussion",
    status: "Scheduled"
  },
  {
    id: 4,
    time: "04:15 PM",
    type: "Call",
    contact: "Sarah Johnson",
    phone: "+91 65432 10987",
    purpose: "Credit Card Inquiry",
    status: "Scheduled"
  }
];

const upcomingWeek = [
  {
    date: "Tomorrow",
    count: 8,
    important: 3
  },
  {
    date: "Wednesday",
    count: 12,
    important: 5
  },
  {
    date: "Thursday",
    count: 6,
    important: 2
  },
  {
    date: "Friday",
    count: 15,
    important: 7
  }
];

export default function Schedule() {
  const getStatusColor = (status: string) => {
    switch (status) {
      case "Completed": return "bg-success text-success-foreground";
      case "Scheduled": return "bg-primary text-primary-foreground";
      case "Missed": return "bg-destructive text-destructive-foreground";
      default: return "bg-muted text-muted-foreground";
    }
  };

  const getTypeIcon = (type: string) => {
    switch (type) {
      case "Call": return <Phone className="h-4 w-4" />;
      case "Meeting": return <Video className="h-4 w-4" />;
      case "Message": return <MessageSquare className="h-4 w-4" />;
      default: return <Calendar className="h-4 w-4" />;
    }
  };

  return (
    <Layout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Schedule Manager</h1>
            <p className="text-muted-foreground mt-1">
              Manage your calls, meetings, and follow-ups
            </p>
          </div>
          <Button className="gap-2">
            <Plus className="h-4 w-4" />
            Schedule New
          </Button>
        </div>

        {/* Quick Stats */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Today's Schedule</p>
                  <p className="text-2xl font-bold">8</p>
                </div>
                <div className="w-12 h-12 bg-gradient-primary rounded-lg flex items-center justify-center text-white">
                  <Calendar className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Completed</p>
                  <p className="text-2xl font-bold">3</p>
                </div>
                <div className="w-12 h-12 bg-success rounded-lg flex items-center justify-center text-white">
                  <Clock className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Upcoming</p>
                  <p className="text-2xl font-bold">5</p>
                </div>
                <div className="w-12 h-12 bg-warning rounded-lg flex items-center justify-center text-white">
                  <Calendar className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">This Week</p>
                  <p className="text-2xl font-bold">41</p>
                </div>
                <div className="w-12 h-12 bg-secondary rounded-lg flex items-center justify-center text-white">
                  <Calendar className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Today's Schedule */}
          <Card className="lg:col-span-2">
            <CardHeader>
              <CardTitle>Today's Schedule</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {todaySchedule.map((item) => (
                  <div key={item.id} className="flex items-center space-x-4 p-4 border rounded-lg">
                    <div className="flex items-center justify-center w-12 h-12 bg-gradient-primary rounded-lg text-white">
                      {getTypeIcon(item.type)}
                    </div>
                    
                    <div className="flex-1 space-y-1">
                      <div className="flex items-center space-x-2">
                        <p className="font-semibold">{item.contact}</p>
                        <Badge className={getStatusColor(item.status)}>
                          {item.status}
                        </Badge>
                      </div>
                      <p className="text-sm text-muted-foreground">{item.purpose}</p>
                      <p className="text-sm text-muted-foreground">{item.phone}</p>
                    </div>
                    
                    <div className="text-right space-y-1">
                      <p className="font-medium">{item.time}</p>
                      <p className="text-sm text-muted-foreground">{item.type}</p>
                    </div>
                    
                    <div className="flex flex-col space-y-2">
                      {item.status === "Scheduled" && (
                        <>
                          <Button size="sm" className="gap-2">
                            <Phone className="h-3 w-3" />
                            Call
                          </Button>
                          <Button size="sm" variant="outline">
                            Reschedule
                          </Button>
                        </>
                      )}
                      {item.status === "Completed" && (
                        <Button size="sm" variant="outline">
                          View Notes
                        </Button>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Upcoming Week */}
          <Card>
            <CardHeader>
              <CardTitle>Upcoming Week</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {upcomingWeek.map((day, index) => (
                  <div key={index} className="flex items-center justify-between p-3 border rounded-lg">
                    <div>
                      <p className="font-medium">{day.date}</p>
                      <p className="text-sm text-muted-foreground">
                        {day.count} appointments
                      </p>
                    </div>
                    <div className="text-right">
                      <p className="text-lg font-bold">{day.count}</p>
                      <p className="text-sm text-destructive">
                        {day.important} urgent
                      </p>
                    </div>
                  </div>
                ))}
              </div>
              
              <Button className="w-full mt-4" variant="outline">
                View Full Calendar
              </Button>
            </CardContent>
          </Card>
        </div>

        {/* Quick Actions */}
        <Card>
          <CardHeader>
            <CardTitle>Quick Schedule</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Card className="p-4 cursor-pointer hover:shadow-medium transition-shadow">
                <div className="text-center space-y-2">
                  <div className="w-12 h-12 bg-gradient-primary rounded-lg flex items-center justify-center text-white mx-auto">
                    <Phone className="h-6 w-6" />
                  </div>
                  <h3 className="font-semibold">Schedule Call</h3>
                  <p className="text-sm text-muted-foreground">
                    Book a follow-up call with a lead
                  </p>
                </div>
              </Card>
              
              <Card className="p-4 cursor-pointer hover:shadow-medium transition-shadow">
                <div className="text-center space-y-2">
                  <div className="w-12 h-12 bg-gradient-success rounded-lg flex items-center justify-center text-white mx-auto">
                    <Video className="h-6 w-6" />
                  </div>
                  <h3 className="font-semibold">Book Meeting</h3>
                  <p className="text-sm text-muted-foreground">
                    Schedule a video meeting
                  </p>
                </div>
              </Card>
              
              <Card className="p-4 cursor-pointer hover:shadow-medium transition-shadow">
                <div className="text-center space-y-2">
                  <div className="w-12 h-12 bg-warning rounded-lg flex items-center justify-center text-white mx-auto">
                    <Clock className="h-6 w-6" />
                  </div>
                  <h3 className="font-semibold">Set Reminder</h3>
                  <p className="text-sm text-muted-foreground">
                    Create a follow-up reminder
                  </p>
                </div>
              </Card>
            </div>
          </CardContent>
        </Card>
      </div>
    </Layout>
  );
}