import { Plus, Play, Pause, Target, TrendingUp, Users, Phone } from "lucide-react";
import Layout from "@/components/layout/Layout";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Progress } from "@/components/ui/progress";

const campaigns = [
  {
    id: 1,
    name: "Home Loan Q1 2024",
    type: "Outbound",
    status: "Active",
    leads: 1250,
    called: 847,
    converted: 156,
    conversionRate: 18.4,
    startDate: "2024-01-01",
    endDate: "2024-03-31"
  },
  {
    id: 2,
    name: "Personal Loan Winter",
    type: "Follow-up",
    status: "Paused",
    leads: 890,
    called: 445,
    converted: 89,
    conversionRate: 20.0,
    startDate: "2024-01-15",
    endDate: "2024-02-29"
  },
  {
    id: 3,
    name: "Credit Card Promotion",
    type: "Warm Leads",
    status: "Completed",
    leads: 650,
    called: 650,
    converted: 97,
    conversionRate: 14.9,
    startDate: "2023-12-01",
    endDate: "2023-12-31"
  }
];

export default function Campaigns() {
  const getStatusColor = (status: string) => {
    switch (status) {
      case "Active": return "bg-success text-success-foreground";
      case "Paused": return "bg-warning text-warning-foreground";
      case "Completed": return "bg-muted text-muted-foreground";
      default: return "bg-muted text-muted-foreground";
    }
  };

  const getTypeColor = (type: string) => {
    switch (type) {
      case "Outbound": return "bg-primary text-primary-foreground";
      case "Follow-up": return "bg-secondary text-secondary-foreground";
      case "Warm Leads": return "bg-accent text-accent-foreground";
      default: return "bg-muted text-muted-foreground";
    }
  };

  return (
    <Layout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Campaign Manager</h1>
            <p className="text-muted-foreground mt-1">
              Create and manage your telecalling campaigns
            </p>
          </div>
          <Button className="gap-2">
            <Plus className="h-4 w-4" />
            New Campaign
          </Button>
        </div>

        {/* Stats Overview */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Active Campaigns</p>
                  <p className="text-2xl font-bold">8</p>
                </div>
                <div className="w-12 h-12 bg-gradient-primary rounded-lg flex items-center justify-center text-white">
                  <Target className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Total Leads</p>
                  <p className="text-2xl font-bold">3,247</p>
                </div>
                <div className="w-12 h-12 bg-secondary rounded-lg flex items-center justify-center text-white">
                  <Users className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Calls Made</p>
                  <p className="text-2xl font-bold">1,942</p>
                </div>
                <div className="w-12 h-12 bg-success rounded-lg flex items-center justify-center text-white">
                  <Phone className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Avg. Conversion</p>
                  <p className="text-2xl font-bold">17.8%</p>
                </div>
                <div className="w-12 h-12 bg-warning rounded-lg flex items-center justify-center text-white">
                  <TrendingUp className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Campaigns List */}
        <div className="space-y-4">
          {campaigns.map((campaign) => (
            <Card key={campaign.id}>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-4">
                    <CardTitle>{campaign.name}</CardTitle>
                    <Badge className={getTypeColor(campaign.type)}>
                      {campaign.type}
                    </Badge>
                    <Badge className={getStatusColor(campaign.status)}>
                      {campaign.status}
                    </Badge>
                  </div>
                  <div className="flex items-center space-x-2">
                    {campaign.status === "Active" && (
                      <Button size="sm" variant="outline" className="gap-2">
                        <Pause className="h-4 w-4" />
                        Pause
                      </Button>
                    )}
                    {campaign.status === "Paused" && (
                      <Button size="sm" className="gap-2">
                        <Play className="h-4 w-4" />
                        Resume
                      </Button>
                    )}
                    <Button size="sm" variant="outline">
                      View Details
                    </Button>
                  </div>
                </div>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                  {/* Progress */}
                  <div className="space-y-2">
                    <div className="flex justify-between text-sm">
                      <span>Progress</span>
                      <span>{Math.round((campaign.called / campaign.leads) * 100)}%</span>
                    </div>
                    <Progress value={(campaign.called / campaign.leads) * 100} />
                    <p className="text-sm text-muted-foreground">
                      {campaign.called} of {campaign.leads} leads called
                    </p>
                  </div>

                  {/* Conversion Stats */}
                  <div className="space-y-2">
                    <p className="text-sm font-medium">Conversions</p>
                    <p className="text-2xl font-bold text-success">{campaign.converted}</p>
                    <p className="text-sm text-muted-foreground">
                      {campaign.conversionRate}% conversion rate
                    </p>
                  </div>

                  {/* Timeline */}
                  <div className="space-y-2">
                    <p className="text-sm font-medium">Timeline</p>
                    <p className="text-sm text-muted-foreground">
                      Start: {new Date(campaign.startDate).toLocaleDateString()}
                    </p>
                    <p className="text-sm text-muted-foreground">
                      End: {new Date(campaign.endDate).toLocaleDateString()}
                    </p>
                  </div>

                  {/* Quick Actions */}
                  <div className="space-y-2">
                    <p className="text-sm font-medium">Quick Actions</p>
                    <div className="space-y-2">
                      <Button size="sm" variant="outline" className="w-full">
                        View Reports
                      </Button>
                      <Button size="sm" variant="outline" className="w-full">
                        Export Data
                      </Button>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Quick Campaign Creation */}
        <Card>
          <CardHeader>
            <CardTitle>Quick Campaign Templates</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Card className="p-4 cursor-pointer hover:shadow-medium transition-shadow">
                <div className="text-center space-y-2">
                  <div className="w-12 h-12 bg-gradient-primary rounded-lg flex items-center justify-center text-white mx-auto">
                    <Phone className="h-6 w-6" />
                  </div>
                  <h3 className="font-semibold">Cold Calling</h3>
                  <p className="text-sm text-muted-foreground">
                    Start calling new leads from your database
                  </p>
                </div>
              </Card>
              
              <Card className="p-4 cursor-pointer hover:shadow-medium transition-shadow">
                <div className="text-center space-y-2">
                  <div className="w-12 h-12 bg-gradient-success rounded-lg flex items-center justify-center text-white mx-auto">
                    <Target className="h-6 w-6" />
                  </div>
                  <h3 className="font-semibold">Follow-up Campaign</h3>
                  <p className="text-sm text-muted-foreground">
                    Re-engage with previous contacts
                  </p>
                </div>
              </Card>
              
              <Card className="p-4 cursor-pointer hover:shadow-medium transition-shadow">
                <div className="text-center space-y-2">
                  <div className="w-12 h-12 bg-warning rounded-lg flex items-center justify-center text-white mx-auto">
                    <TrendingUp className="h-6 w-6" />
                  </div>
                  <h3 className="font-semibold">Warm Leads</h3>
                  <p className="text-sm text-muted-foreground">
                    Call interested prospects
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