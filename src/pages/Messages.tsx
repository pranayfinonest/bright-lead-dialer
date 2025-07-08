import { Send, Plus, Search, MessageSquare, Phone, Mail } from "lucide-react";
import Layout from "@/components/layout/Layout";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";

const whatsappTemplates = [
  {
    id: 1,
    name: "Home Loan Introduction",
    message: "Hi {name}, I'm calling regarding your interest in home loans. Our special rates start from 8.5% PA. Are you available for a quick call?",
    category: "Introduction",
    usage: 245
  },
  {
    id: 2,
    name: "Follow-up Reminder",
    message: "Hello {name}, Hope you're doing well. Just following up on our conversation about {product}. When would be a good time to discuss further?",
    category: "Follow-up",
    usage: 189
  },
  {
    id: 3,
    name: "Document Request",
    message: "Hi {name}, To proceed with your {product} application, we need the following documents: {documents}. Please share them at your convenience.",
    category: "Documents",
    usage: 156
  }
];

const smsTemplates = [
  {
    id: 1,
    name: "Appointment Reminder",
    message: "Dear {name}, This is a reminder about your appointment scheduled for {date} at {time}. Please confirm your availability. - TeleCRM",
    category: "Reminder",
    usage: 178
  },
  {
    id: 2,
    name: "Thank You Message",
    message: "Thank you {name} for choosing our services. Your application reference number is {ref_number}. We'll contact you within 24 hours.",
    category: "Confirmation",
    usage: 134
  }
];

const emailTemplates = [
  {
    id: 1,
    name: "Loan Proposal",
    subject: "Exclusive Home Loan Offer - {name}",
    message: "Dear {name},\n\nWe have a special home loan offer tailored for you...",
    category: "Proposal",
    usage: 89
  }
];

export default function Messages() {
  return (
    <Layout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Message Center</h1>
            <p className="text-muted-foreground mt-1">
              Manage WhatsApp, SMS, and Email templates
            </p>
          </div>
          <Button className="gap-2">
            <Plus className="h-4 w-4" />
            Create Template
          </Button>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">WhatsApp Sent</p>
                  <p className="text-2xl font-bold">1,247</p>
                </div>
                <div className="w-12 h-12 bg-success rounded-lg flex items-center justify-center text-white">
                  <MessageSquare className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">SMS Sent</p>
                  <p className="text-2xl font-bold">892</p>
                </div>
                <div className="w-12 h-12 bg-primary rounded-lg flex items-center justify-center text-white">
                  <Phone className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Emails Sent</p>
                  <p className="text-2xl font-bold">456</p>
                </div>
                <div className="w-12 h-12 bg-secondary rounded-lg flex items-center justify-center text-white">
                  <Mail className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Response Rate</p>
                  <p className="text-2xl font-bold">23.4%</p>
                </div>
                <div className="w-12 h-12 bg-warning rounded-lg flex items-center justify-center text-white">
                  <MessageSquare className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Templates */}
        <Card>
          <CardHeader>
            <CardTitle>Message Templates</CardTitle>
          </CardHeader>
          <CardContent>
            <Tabs defaultValue="whatsapp" className="space-y-6">
              <TabsList className="grid w-full grid-cols-3">
                <TabsTrigger value="whatsapp">WhatsApp</TabsTrigger>
                <TabsTrigger value="sms">SMS</TabsTrigger>
                <TabsTrigger value="email">Email</TabsTrigger>
              </TabsList>

              {/* Search */}
              <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Search templates..."
                  className="pl-10"
                />
              </div>

              <TabsContent value="whatsapp" className="space-y-4">
                {whatsappTemplates.map((template) => (
                  <Card key={template.id}>
                    <CardContent className="p-4">
                      <div className="flex items-start justify-between">
                        <div className="flex-1 space-y-2">
                          <div className="flex items-center space-x-2">
                            <h3 className="font-semibold">{template.name}</h3>
                            <Badge variant="outline">{template.category}</Badge>
                            <span className="text-sm text-muted-foreground">
                              Used {template.usage} times
                            </span>
                          </div>
                          <p className="text-sm text-muted-foreground max-w-2xl">
                            {template.message}
                          </p>
                        </div>
                        <div className="flex space-x-2">
                          <Button size="sm" variant="outline">Edit</Button>
                          <Button size="sm">Use Template</Button>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </TabsContent>

              <TabsContent value="sms" className="space-y-4">
                {smsTemplates.map((template) => (
                  <Card key={template.id}>
                    <CardContent className="p-4">
                      <div className="flex items-start justify-between">
                        <div className="flex-1 space-y-2">
                          <div className="flex items-center space-x-2">
                            <h3 className="font-semibold">{template.name}</h3>
                            <Badge variant="outline">{template.category}</Badge>
                            <span className="text-sm text-muted-foreground">
                              Used {template.usage} times
                            </span>
                          </div>
                          <p className="text-sm text-muted-foreground max-w-2xl">
                            {template.message}
                          </p>
                        </div>
                        <div className="flex space-x-2">
                          <Button size="sm" variant="outline">Edit</Button>
                          <Button size="sm">Use Template</Button>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </TabsContent>

              <TabsContent value="email" className="space-y-4">
                {emailTemplates.map((template) => (
                  <Card key={template.id}>
                    <CardContent className="p-4">
                      <div className="flex items-start justify-between">
                        <div className="flex-1 space-y-2">
                          <div className="flex items-center space-x-2">
                            <h3 className="font-semibold">{template.name}</h3>
                            <Badge variant="outline">{template.category}</Badge>
                            <span className="text-sm text-muted-foreground">
                              Used {template.usage} times
                            </span>
                          </div>
                          <p className="text-sm font-medium">Subject: {template.subject}</p>
                          <p className="text-sm text-muted-foreground max-w-2xl">
                            {template.message}
                          </p>
                        </div>
                        <div className="flex space-x-2">
                          <Button size="sm" variant="outline">Edit</Button>
                          <Button size="sm">Use Template</Button>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </TabsContent>
            </Tabs>
          </CardContent>
        </Card>

        {/* Quick Send */}
        <Card>
          <CardHeader>
            <CardTitle>Quick Send Message</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="text-sm font-medium">To (Phone/Email)</label>
                <Input placeholder="+91 98765 43210" />
              </div>
              <div>
                <label className="text-sm font-medium">Message Type</label>
                <select className="w-full p-2 border rounded-md">
                  <option>WhatsApp</option>
                  <option>SMS</option>
                  <option>Email</option>
                </select>
              </div>
            </div>
            <div>
              <label className="text-sm font-medium">Message</label>
              <Textarea 
                placeholder="Type your message here..."
                className="min-h-[100px]"
              />
            </div>
            <Button className="gap-2">
              <Send className="h-4 w-4" />
              Send Message
            </Button>
          </CardContent>
        </Card>
      </div>
    </Layout>
  );
}