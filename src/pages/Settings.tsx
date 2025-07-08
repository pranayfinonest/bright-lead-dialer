import { Save, User, Bell, Phone, Shield, Database, Palette } from "lucide-react";
import Layout from "@/components/layout/Layout";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Textarea } from "@/components/ui/textarea";

export default function Settings() {
  return (
    <Layout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Settings</h1>
            <p className="text-muted-foreground mt-1">
              Configure your TeleCRM preferences and system settings
            </p>
          </div>
          <Button className="gap-2">
            <Save className="h-4 w-4" />
            Save Changes
          </Button>
        </div>

        {/* Settings Tabs */}
        <Card>
          <CardContent className="p-6">
            <Tabs defaultValue="profile" className="space-y-6">
              <TabsList className="grid w-full grid-cols-6">
                <TabsTrigger value="profile">Profile</TabsTrigger>
                <TabsTrigger value="notifications">Notifications</TabsTrigger>
                <TabsTrigger value="calling">Calling</TabsTrigger>
                <TabsTrigger value="security">Security</TabsTrigger>
                <TabsTrigger value="integrations">Integrations</TabsTrigger>
                <TabsTrigger value="appearance">Appearance</TabsTrigger>
              </TabsList>

              <TabsContent value="profile" className="space-y-6">
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <User className="h-5 w-5" />
                      Profile Information
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <Label htmlFor="firstName">First Name</Label>
                        <Input id="firstName" defaultValue="Sales" />
                      </div>
                      <div>
                        <Label htmlFor="lastName">Last Name</Label>
                        <Input id="lastName" defaultValue="Agent" />
                      </div>
                    </div>
                    <div>
                      <Label htmlFor="email">Email</Label>
                      <Input id="email" type="email" defaultValue="agent@company.com" />
                    </div>
                    <div>
                      <Label htmlFor="phone">Phone Number</Label>
                      <Input id="phone" defaultValue="+91 98765 43210" />
                    </div>
                    <div>
                      <Label htmlFor="bio">Bio</Label>
                      <Textarea id="bio" placeholder="Tell us about yourself..." />
                    </div>
                  </CardContent>
                </Card>

                <Card>
                  <CardHeader>
                    <CardTitle>Agent Settings</CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div>
                      <Label htmlFor="agentId">Agent ID</Label>
                      <Input id="agentId" defaultValue="SA001" disabled />
                    </div>
                    <div>
                      <Label htmlFor="department">Department</Label>
                      <select className="w-full p-2 border rounded-md">
                        <option>Sales</option>
                        <option>Customer Support</option>
                        <option>Follow-up</option>
                      </select>
                    </div>
                    <div>
                      <Label htmlFor="shift">Work Shift</Label>
                      <select className="w-full p-2 border rounded-md">
                        <option>Morning (9 AM - 6 PM)</option>
                        <option>Evening (2 PM - 11 PM)</option>
                        <option>Night (6 PM - 3 AM)</option>
                      </select>
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>

              <TabsContent value="notifications" className="space-y-6">
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <Bell className="h-5 w-5" />
                      Notification Preferences
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-6">
                    <div className="space-y-4">
                      <div className="flex items-center justify-between">
                        <div>
                          <p className="font-medium">Lead Assignments</p>
                          <p className="text-sm text-muted-foreground">Get notified when new leads are assigned</p>
                        </div>
                        <Switch defaultChecked />
                      </div>
                      
                      <div className="flex items-center justify-between">
                        <div>
                          <p className="font-medium">Callback Reminders</p>
                          <p className="text-sm text-muted-foreground">Reminders for scheduled callbacks</p>
                        </div>
                        <Switch defaultChecked />
                      </div>
                      
                      <div className="flex items-center justify-between">
                        <div>
                          <p className="font-medium">Campaign Updates</p>
                          <p className="text-sm text-muted-foreground">Updates about campaign performance</p>
                        </div>
                        <Switch />
                      </div>
                      
                      <div className="flex items-center justify-between">
                        <div>
                          <p className="font-medium">System Alerts</p>
                          <p className="text-sm text-muted-foreground">Important system notifications</p>
                        </div>
                        <Switch defaultChecked />
                      </div>
                    </div>

                    <div className="border-t pt-4">
                      <h3 className="font-medium mb-4">Notification Methods</h3>
                      <div className="space-y-4">
                        <div className="flex items-center justify-between">
                          <span>Email Notifications</span>
                          <Switch defaultChecked />
                        </div>
                        <div className="flex items-center justify-between">
                          <span>Push Notifications</span>
                          <Switch defaultChecked />
                        </div>
                        <div className="flex items-center justify-between">
                          <span>SMS Notifications</span>
                          <Switch />
                        </div>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>

              <TabsContent value="calling" className="space-y-6">
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <Phone className="h-5 w-5" />
                      Calling Preferences
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-6">
                    <div>
                      <Label htmlFor="dialerMode">Auto Dialer Mode</Label>
                      <select className="w-full p-2 border rounded-md mt-1">
                        <option>Progressive Dialer</option>
                        <option>Predictive Dialer</option>
                        <option>Power Dialer</option>
                        <option>Manual Dialer</option>
                      </select>
                    </div>

                    <div>
                      <Label htmlFor="callTimeout">Call Timeout (seconds)</Label>
                      <Input id="callTimeout" type="number" defaultValue="30" />
                    </div>

                    <div>
                      <Label htmlFor="dialPrefix">Dial Prefix</Label>
                      <Input id="dialPrefix" placeholder="e.g., 0 or 9" />
                    </div>

                    <div className="space-y-4">
                      <div className="flex items-center justify-between">
                        <div>
                          <p className="font-medium">Auto-Record Calls</p>
                          <p className="text-sm text-muted-foreground">Automatically record all outgoing calls</p>
                        </div>
                        <Switch defaultChecked />
                      </div>
                      
                      <div className="flex items-center justify-between">
                        <div>
                          <p className="font-medium">Caller ID Display</p>
                          <p className="text-sm text-muted-foreground">Show caller information during calls</p>
                        </div>
                        <Switch defaultChecked />
                      </div>
                      
                      <div className="flex items-center justify-between">
                        <div>
                          <p className="font-medium">Call Notes Prompt</p>
                          <p className="text-sm text-muted-foreground">Prompt to add notes after each call</p>
                        </div>
                        <Switch defaultChecked />
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>

              <TabsContent value="security" className="space-y-6">
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <Shield className="h-5 w-5" />
                      Security Settings
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-6">
                    <div>
                      <h3 className="font-medium mb-4">Change Password</h3>
                      <div className="space-y-4">
                        <div>
                          <Label htmlFor="currentPassword">Current Password</Label>
                          <Input id="currentPassword" type="password" />
                        </div>
                        <div>
                          <Label htmlFor="newPassword">New Password</Label>
                          <Input id="newPassword" type="password" />
                        </div>
                        <div>
                          <Label htmlFor="confirmPassword">Confirm New Password</Label>
                          <Input id="confirmPassword" type="password" />
                        </div>
                        <Button>Update Password</Button>
                      </div>
                    </div>

                    <div className="border-t pt-4">
                      <h3 className="font-medium mb-4">Two-Factor Authentication</h3>
                      <div className="space-y-4">
                        <div className="flex items-center justify-between">
                          <div>
                            <p className="font-medium">Enable 2FA</p>
                            <p className="text-sm text-muted-foreground">Add an extra layer of security</p>
                          </div>
                          <Switch />
                        </div>
                      </div>
                    </div>

                    <div className="border-t pt-4">
                      <h3 className="font-medium mb-4">Session Management</h3>
                      <div className="space-y-4">
                        <div>
                          <Label>Auto Logout (minutes)</Label>
                          <Input type="number" defaultValue="60" />
                        </div>
                        <Button variant="outline">View Active Sessions</Button>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>

              <TabsContent value="integrations" className="space-y-6">
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <Database className="h-5 w-5" />
                      Third-Party Integrations
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <Card className="p-4">
                        <div className="flex items-center justify-between">
                          <div>
                            <h3 className="font-semibold">WhatsApp Business</h3>
                            <p className="text-sm text-muted-foreground">Send messages via WhatsApp</p>
                          </div>
                          <Switch />
                        </div>
                      </Card>

                      <Card className="p-4">
                        <div className="flex items-center justify-between">
                          <div>
                            <h3 className="font-semibold">SMS Gateway</h3>
                            <p className="text-sm text-muted-foreground">SMS notifications and updates</p>
                          </div>
                          <Switch defaultChecked />
                        </div>
                      </Card>

                      <Card className="p-4">
                        <div className="flex items-center justify-between">
                          <div>
                            <h3 className="font-semibold">Email Service</h3>
                            <p className="text-sm text-muted-foreground">Email campaigns and follow-ups</p>
                          </div>
                          <Switch defaultChecked />
                        </div>
                      </Card>

                      <Card className="p-4">
                        <div className="flex items-center justify-between">
                          <div>
                            <h3 className="font-semibold">CRM Integration</h3>
                            <p className="text-sm text-muted-foreground">Sync with external CRM</p>
                          </div>
                          <Switch />
                        </div>
                      </Card>
                    </div>

                    <div className="border-t pt-4">
                      <h3 className="font-medium mb-4">API Settings</h3>
                      <div className="space-y-4">
                        <div>
                          <Label>API Endpoint</Label>
                          <Input defaultValue="https://api.telecrm.com/v1" />
                        </div>
                        <div>
                          <Label>API Key</Label>
                          <Input type="password" defaultValue="sk_live_..." />
                        </div>
                        <Button variant="outline">Test Connection</Button>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>

              <TabsContent value="appearance" className="space-y-6">
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <Palette className="h-5 w-5" />
                      Appearance Settings
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-6">
                    <div>
                      <Label>Theme</Label>
                      <select className="w-full p-2 border rounded-md mt-1">
                        <option>Light</option>
                        <option>Dark</option>
                        <option>System</option>
                      </select>
                    </div>

                    <div>
                      <Label>Language</Label>
                      <select className="w-full p-2 border rounded-md mt-1">
                        <option>English</option>
                        <option>Hindi</option>
                        <option>Tamil</option>
                        <option>Telugu</option>
                      </select>
                    </div>

                    <div>
                      <Label>Date Format</Label>
                      <select className="w-full p-2 border rounded-md mt-1">
                        <option>DD/MM/YYYY</option>
                        <option>MM/DD/YYYY</option>
                        <option>YYYY-MM-DD</option>
                      </select>
                    </div>

                    <div>
                      <Label>Time Format</Label>
                      <select className="w-full p-2 border rounded-md mt-1">
                        <option>12 Hour (AM/PM)</option>
                        <option>24 Hour</option>
                      </select>
                    </div>

                    <div className="space-y-4">
                      <div className="flex items-center justify-between">
                        <div>
                          <p className="font-medium">Compact Mode</p>
                          <p className="text-sm text-muted-foreground">Show more content in less space</p>
                        </div>
                        <Switch />
                      </div>
                      
                      <div className="flex items-center justify-between">
                        <div>
                          <p className="font-medium">Show Animations</p>
                          <p className="text-sm text-muted-foreground">Enable smooth transitions and animations</p>
                        </div>
                        <Switch defaultChecked />
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>
            </Tabs>
          </CardContent>
        </Card>
      </div>
    </Layout>
  );
}