import { TrendingUp, Phone, Users, Target, Download, Calendar } from "lucide-react";
import Layout from "@/components/layout/Layout";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";

export default function Analytics() {
  return (
    <Layout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Analytics Dashboard</h1>
            <p className="text-muted-foreground mt-1">
              Track performance and gain insights from your calling activities
            </p>
          </div>
          <div className="flex space-x-2">
            <Button variant="outline" className="gap-2">
              <Calendar className="h-4 w-4" />
              Last 30 Days
            </Button>
            <Button className="gap-2">
              <Download className="h-4 w-4" />
              Export Report
            </Button>
          </div>
        </div>

        {/* Key Metrics */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Total Calls</p>
                  <p className="text-2xl font-bold">2,847</p>
                  <p className="text-sm text-success">↗ +12.5%</p>
                </div>
                <div className="w-12 h-12 bg-gradient-primary rounded-lg flex items-center justify-center text-white">
                  <Phone className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Conversion Rate</p>
                  <p className="text-2xl font-bold">18.3%</p>
                  <p className="text-sm text-success">↗ +2.1%</p>
                </div>
                <div className="w-12 h-12 bg-success rounded-lg flex items-center justify-center text-white">
                  <Target className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Avg Call Duration</p>
                  <p className="text-2xl font-bold">4:32</p>
                  <p className="text-sm text-success">↗ +0:45</p>
                </div>
                <div className="w-12 h-12 bg-warning rounded-lg flex items-center justify-center text-white">
                  <Phone className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Revenue Generated</p>
                  <p className="text-2xl font-bold">₹8.4L</p>
                  <p className="text-sm text-success">↗ +18.7%</p>
                </div>
                <div className="w-12 h-12 bg-secondary rounded-lg flex items-center justify-center text-white">
                  <TrendingUp className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Analytics Tabs */}
        <Card>
          <CardHeader>
            <CardTitle>Detailed Analytics</CardTitle>
          </CardHeader>
          <CardContent>
            <Tabs defaultValue="performance" className="space-y-6">
              <TabsList className="grid w-full grid-cols-4">
                <TabsTrigger value="performance">Performance</TabsTrigger>
                <TabsTrigger value="agents">Agents</TabsTrigger>
                <TabsTrigger value="campaigns">Campaigns</TabsTrigger>
                <TabsTrigger value="trends">Trends</TabsTrigger>
              </TabsList>

              <TabsContent value="performance" className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  {/* Call Success Rate */}
                  <Card>
                    <CardHeader>
                      <CardTitle className="text-lg">Call Success Rate</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="space-y-4">
                        <div className="flex justify-between items-center">
                          <span>Successful Calls</span>
                          <span className="text-success font-semibold">72%</span>
                        </div>
                        <div className="w-full bg-muted rounded-full h-2">
                          <div className="bg-success h-2 rounded-full" style={{ width: '72%' }}></div>
                        </div>
                        
                        <div className="flex justify-between items-center">
                          <span>No Answer</span>
                          <span className="text-warning font-semibold">18%</span>
                        </div>
                        <div className="w-full bg-muted rounded-full h-2">
                          <div className="bg-warning h-2 rounded-full" style={{ width: '18%' }}></div>
                        </div>
                        
                        <div className="flex justify-between items-center">
                          <span>Call Failed</span>
                          <span className="text-destructive font-semibold">10%</span>
                        </div>
                        <div className="w-full bg-muted rounded-full h-2">
                          <div className="bg-destructive h-2 rounded-full" style={{ width: '10%' }}></div>
                        </div>
                      </div>
                    </CardContent>
                  </Card>

                  {/* Hourly Performance */}
                  <Card>
                    <CardHeader>
                      <CardTitle className="text-lg">Best Calling Hours</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="space-y-3">
                        <div className="flex justify-between items-center p-2 bg-success/10 rounded">
                          <span>10:00 AM - 12:00 PM</span>
                          <Badge className="bg-success text-success-foreground">Best</Badge>
                        </div>
                        <div className="flex justify-between items-center p-2 bg-primary/10 rounded">
                          <span>2:00 PM - 4:00 PM</span>
                          <Badge className="bg-primary text-primary-foreground">Good</Badge>
                        </div>
                        <div className="flex justify-between items-center p-2 bg-warning/10 rounded">
                          <span>4:00 PM - 6:00 PM</span>
                          <Badge className="bg-warning text-warning-foreground">Average</Badge>
                        </div>
                        <div className="flex justify-between items-center p-2 bg-muted/10 rounded">
                          <span>6:00 PM - 8:00 PM</span>
                          <Badge variant="outline">Low</Badge>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                </div>
              </TabsContent>

              <TabsContent value="agents" className="space-y-6">
                <div className="space-y-4">
                  {[
                    { name: "Agent A", calls: 156, conversions: 28, rate: "17.9%", revenue: "₹2.4L" },
                    { name: "Agent B", calls: 143, conversions: 31, rate: "21.7%", revenue: "₹3.1L" },
                    { name: "Agent C", calls: 138, conversions: 25, rate: "18.1%", revenue: "₹2.8L" },
                    { name: "Agent D", calls: 121, conversions: 19, rate: "15.7%", revenue: "₹1.9L" }
                  ].map((agent, index) => (
                    <Card key={index}>
                      <CardContent className="p-4">
                        <div className="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
                          <div className="flex items-center space-x-3">
                            <div className="w-10 h-10 bg-gradient-primary rounded-full flex items-center justify-center text-white font-semibold">
                              {agent.name.split(' ')[1]}
                            </div>
                            <span className="font-semibold">{agent.name}</span>
                          </div>
                          <div className="text-center">
                            <p className="text-2xl font-bold">{agent.calls}</p>
                            <p className="text-sm text-muted-foreground">Calls</p>
                          </div>
                          <div className="text-center">
                            <p className="text-2xl font-bold text-success">{agent.conversions}</p>
                            <p className="text-sm text-muted-foreground">Conversions</p>
                          </div>
                          <div className="text-center">
                            <p className="text-2xl font-bold">{agent.rate}</p>
                            <p className="text-sm text-muted-foreground">Success Rate</p>
                          </div>
                          <div className="text-center">
                            <p className="text-2xl font-bold text-primary">{agent.revenue}</p>
                            <p className="text-sm text-muted-foreground">Revenue</p>
                          </div>
                        </div>
                      </CardContent>
                    </Card>
                  ))}
                </div>
              </TabsContent>

              <TabsContent value="campaigns" className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  {[
                    { name: "Home Loan Q1", calls: 1250, conversions: 230, rate: "18.4%", status: "Active" },
                    { name: "Personal Loan Winter", calls: 890, conversions: 178, rate: "20.0%", status: "Completed" },
                    { name: "Credit Card Promo", calls: 650, conversions: 97, rate: "14.9%", status: "Paused" },
                    { name: "Business Loan Drive", calls: 420, conversions: 89, rate: "21.2%", status: "Active" }
                  ].map((campaign, index) => (
                    <Card key={index}>
                      <CardContent className="p-4">
                        <div className="space-y-3">
                          <div className="flex justify-between items-center">
                            <h3 className="font-semibold">{campaign.name}</h3>
                            <Badge 
                              className={
                                campaign.status === "Active" ? "bg-success text-success-foreground" :
                                campaign.status === "Completed" ? "bg-muted text-muted-foreground" :
                                "bg-warning text-warning-foreground"
                              }
                            >
                              {campaign.status}
                            </Badge>
                          </div>
                          <div className="grid grid-cols-3 gap-4 text-center">
                            <div>
                              <p className="text-lg font-bold">{campaign.calls}</p>
                              <p className="text-xs text-muted-foreground">Calls</p>
                            </div>
                            <div>
                              <p className="text-lg font-bold text-success">{campaign.conversions}</p>
                              <p className="text-xs text-muted-foreground">Conversions</p>
                            </div>
                            <div>
                              <p className="text-lg font-bold">{campaign.rate}</p>
                              <p className="text-xs text-muted-foreground">Rate</p>
                            </div>
                          </div>
                        </div>
                      </CardContent>
                    </Card>
                  ))}
                </div>
              </TabsContent>

              <TabsContent value="trends" className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <Card>
                    <CardHeader>
                      <CardTitle className="text-lg">Weekly Trends</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="space-y-4">
                        {[
                          { day: "Monday", calls: 320, trend: "+12%" },
                          { day: "Tuesday", calls: 285, trend: "-8%" },
                          { day: "Wednesday", calls: 410, trend: "+25%" },
                          { day: "Thursday", calls: 380, trend: "+18%" },
                          { day: "Friday", calls: 290, trend: "-5%" }
                        ].map((day, index) => (
                          <div key={index} className="flex justify-between items-center">
                            <span>{day.day}</span>
                            <div className="flex items-center space-x-2">
                              <span className="font-semibold">{day.calls}</span>
                              <span className={`text-sm ${day.trend.startsWith('+') ? 'text-success' : 'text-destructive'}`}>
                                {day.trend}
                              </span>
                            </div>
                          </div>
                        ))}
                      </div>
                    </CardContent>
                  </Card>

                  <Card>
                    <CardHeader>
                      <CardTitle className="text-lg">Conversion Trends</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="space-y-4">
                        {[
                          { period: "This Week", rate: "18.3%", change: "+2.1%" },
                          { period: "Last Week", rate: "16.2%", change: "-1.5%" },
                          { period: "This Month", rate: "17.8%", change: "+3.2%" },
                          { period: "Last Month", rate: "14.6%", change: "-0.8%" }
                        ].map((period, index) => (
                          <div key={index} className="flex justify-between items-center">
                            <span>{period.period}</span>
                            <div className="flex items-center space-x-2">
                              <span className="font-semibold">{period.rate}</span>
                              <span className={`text-sm ${period.change.startsWith('+') ? 'text-success' : 'text-destructive'}`}>
                                {period.change}
                              </span>
                            </div>
                          </div>
                        ))}
                      </div>
                    </CardContent>
                  </Card>
                </div>
              </TabsContent>
            </Tabs>
          </CardContent>
        </Card>
      </div>
    </Layout>
  );
}