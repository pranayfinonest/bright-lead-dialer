import { useState } from "react";
import { Plus, Search, Filter, Phone, Mail, MessageSquare, Calendar, MoreVertical } from "lucide-react";
import Layout from "@/components/layout/Layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

const leads = [
  {
    id: 1,
    name: "Rajesh Kumar",
    phone: "+91 98765 43210",
    email: "rajesh@email.com",
    status: "Hot",
    source: "Website",
    assignedTo: "Agent A",
    lastContact: "2 hours ago",
    nextFollowUp: "Today 3:00 PM"
  },
  {
    id: 2,
    name: "Priya Sharma",
    phone: "+91 87654 32109",
    email: "priya@email.com",
    status: "Warm",
    source: "Referral",
    assignedTo: "Agent B",
    lastContact: "1 day ago",
    nextFollowUp: "Tomorrow 10:00 AM"
  },
  {
    id: 3,
    name: "Amit Patel",
    phone: "+91 76543 21098",
    email: "amit@email.com",
    status: "Cold",
    source: "Social Media",
    assignedTo: "Agent A",
    lastContact: "3 days ago",
    nextFollowUp: "Monday 2:00 PM"
  }
];

export default function Leads() {
  const [searchTerm, setSearchTerm] = useState("");

  const getStatusColor = (status: string) => {
    switch (status) {
      case "Hot": return "bg-destructive text-destructive-foreground";
      case "Warm": return "bg-warning text-warning-foreground";
      case "Cold": return "bg-muted text-muted-foreground";
      default: return "bg-muted text-muted-foreground";
    }
  };

  return (
    <Layout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Lead Management</h1>
            <p className="text-muted-foreground mt-1">
              Manage and track your sales leads effectively
            </p>
          </div>
          <Button className="gap-2">
            <Plus className="h-4 w-4" />
            Add New Lead
          </Button>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Total Leads</p>
                  <p className="text-2xl font-bold">1,247</p>
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
                  <p className="text-sm text-muted-foreground">Hot Leads</p>
                  <p className="text-2xl font-bold">89</p>
                </div>
                <div className="w-12 h-12 bg-destructive rounded-lg flex items-center justify-center text-white">
                  <Phone className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Converted Today</p>
                  <p className="text-2xl font-bold">23</p>
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
                  <p className="text-sm text-muted-foreground">Follow-ups Due</p>
                  <p className="text-2xl font-bold">45</p>
                </div>
                <div className="w-12 h-12 bg-warning rounded-lg flex items-center justify-center text-white">
                  <Calendar className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Filters and Search */}
        <Card>
          <CardHeader>
            <CardTitle>Lead Database</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex items-center space-x-4 mb-6">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Search leads by name, phone, or email..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-10"
                />
              </div>
              <Button variant="outline" className="gap-2">
                <Filter className="h-4 w-4" />
                Filters
              </Button>
            </div>

            {/* Leads Table */}
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Lead Details</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Source</TableHead>
                  <TableHead>Assigned To</TableHead>
                  <TableHead>Last Contact</TableHead>
                  <TableHead>Next Follow-up</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {leads.map((lead) => (
                  <TableRow key={lead.id}>
                    <TableCell>
                      <div>
                        <p className="font-medium">{lead.name}</p>
                        <p className="text-sm text-muted-foreground">{lead.phone}</p>
                        <p className="text-sm text-muted-foreground">{lead.email}</p>
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge className={getStatusColor(lead.status)}>
                        {lead.status}
                      </Badge>
                    </TableCell>
                    <TableCell>{lead.source}</TableCell>
                    <TableCell>{lead.assignedTo}</TableCell>
                    <TableCell>{lead.lastContact}</TableCell>
                    <TableCell>{lead.nextFollowUp}</TableCell>
                    <TableCell>
                      <div className="flex items-center space-x-2">
                        <Button size="icon" variant="ghost">
                          <Phone className="h-4 w-4" />
                        </Button>
                        <Button size="icon" variant="ghost">
                          <Mail className="h-4 w-4" />
                        </Button>
                        <Button size="icon" variant="ghost">
                          <MessageSquare className="h-4 w-4" />
                        </Button>
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button size="icon" variant="ghost">
                              <MoreVertical className="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent>
                            <DropdownMenuItem>Edit Lead</DropdownMenuItem>
                            <DropdownMenuItem>View History</DropdownMenuItem>
                            <DropdownMenuItem>Schedule Call</DropdownMenuItem>
                            <DropdownMenuItem className="text-destructive">
                              Delete Lead
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </div>
    </Layout>
  );
}