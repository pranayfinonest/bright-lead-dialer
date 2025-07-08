import { 
  Phone, 
  Users, 
  Target, 
  TrendingUp, 
  Clock,
  CheckCircle,
  XCircle,
  Calendar
} from "lucide-react";
import Layout from "@/components/layout/Layout";
import StatsCard from "@/components/dashboard/StatsCard";
import QuickActions from "@/components/dashboard/QuickActions";
import RecentActivity from "@/components/dashboard/RecentActivity";

const Index = () => {
  return (
    <Layout>
      <div className="space-y-6">
        {/* Welcome Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">
              Good morning, Sales Agent! 👋
            </h1>
            <p className="text-muted-foreground mt-1">
              Ready to make today productive? You have 23 leads to call.
            </p>
          </div>
          <div className="flex items-center space-x-2 px-4 py-2 bg-gradient-primary rounded-lg text-white shadow-medium">
            <Clock className="h-4 w-4" />
            <span className="text-sm font-medium">Active Session: 2h 15m</span>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <StatsCard
            title="Total Calls Today"
            value="47"
            change="+12%"
            changeType="positive"
            icon={<Phone className="h-6 w-6" />}
            description="vs yesterday"
            gradient
          />
          <StatsCard
            title="Leads Generated"
            value="23"
            change="+8%"
            changeType="positive"
            icon={<Users className="h-6 w-6" />}
            description="this week"
          />
          <StatsCard
            title="Conversion Rate"
            value="18.5%"
            change="+2.3%"
            changeType="positive"
            icon={<Target className="h-6 w-6" />}
            description="monthly average"
          />
          <StatsCard
            title="Revenue Impact"
            value="₹2.4L"
            change="+15%"
            changeType="positive"
            icon={<TrendingUp className="h-6 w-6" />}
            description="this month"
          />
        </div>

        {/* Call Performance Today */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <StatsCard
            title="Successful Calls"
            value="32"
            icon={<CheckCircle className="h-5 w-5" />}
            description="68% success rate"
          />
          <StatsCard
            title="Failed Attempts"
            value="15"
            icon={<XCircle className="h-5 w-5" />}
            description="32% failed rate"
          />
          <StatsCard
            title="Scheduled Callbacks"
            value="8"
            icon={<Calendar className="h-5 w-5" />}
            description="pending today"
          />
        </div>

        {/* Main Content Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Quick Actions - Takes 2 columns */}
          <div className="lg:col-span-2">
            <QuickActions />
          </div>
          
          {/* Recent Activity */}
          <div className="lg:col-span-1">
            <RecentActivity />
          </div>
        </div>

        {/* Today's Priority Section */}
        <div className="bg-gradient-card rounded-lg p-6 shadow-soft border border-border">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-xl font-semibold text-foreground">Today's Priorities</h2>
            <span className="text-sm text-muted-foreground">Updated 5 min ago</span>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="p-4 bg-warning/10 rounded-lg border border-warning/20">
              <h3 className="font-medium text-warning mb-2">High Priority Callbacks</h3>
              <p className="text-sm text-muted-foreground mb-2">3 leads waiting for follow-up</p>
              <p className="text-xs text-warning font-medium">Due before 2:00 PM</p>
            </div>
            <div className="p-4 bg-primary/10 rounded-lg border border-primary/20">
              <h3 className="font-medium text-primary mb-2">New Lead Assignment</h3>
              <p className="text-sm text-muted-foreground mb-2">15 fresh leads from website</p>
              <p className="text-xs text-primary font-medium">Call within 1 hour</p>
            </div>
            <div className="p-4 bg-success/10 rounded-lg border border-success/20">
              <h3 className="font-medium text-success mb-2">Campaign Performance</h3>
              <p className="text-sm text-muted-foreground mb-2">Home Loan Q1 exceeding targets</p>
              <p className="text-xs text-success font-medium">125% of goal achieved</p>
            </div>
          </div>
        </div>
      </div>
    </Layout>
  );
};

export default Index;
