import { useState } from "react";
import { Phone, PhoneCall, PhoneOff, Pause, Play, Volume2, Mic, MicOff } from "lucide-react";
import Layout from "@/components/layout/Layout";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";

export default function Dialer() {
  const [isDialing, setIsDialing] = useState(false);
  const [isMuted, setIsMuted] = useState(false);
  const [callDuration, setCallDuration] = useState("00:00");
  const [phoneNumber, setPhoneNumber] = useState("");

  const currentLead = {
    name: "Rajesh Kumar",
    phone: "+91 98765 43210",
    email: "rajesh@email.com",
    status: "Hot",
    lastContact: "First call",
    notes: "Interested in home loan. Prefers evening calls."
  };

  const dialpadNumbers = [
    ['1', '2', '3'],
    ['4', '5', '6'],
    ['7', '8', '9'],
    ['*', '0', '#']
  ];

  const handleDialpadClick = (number: string) => {
    setPhoneNumber(prev => prev + number);
  };

  const handleCall = () => {
    setIsDialing(!isDialing);
    if (!isDialing) {
      // Start call simulation
      let seconds = 0;
      const timer = setInterval(() => {
        seconds++;
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        setCallDuration(`${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`);
      }, 1000);
      
      // Store timer to clear later
      (window as any).callTimer = timer;
    } else {
      // End call
      if ((window as any).callTimer) {
        clearInterval((window as any).callTimer);
      }
      setCallDuration("00:00");
    }
  };

  return (
    <Layout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Auto Dialer</h1>
            <p className="text-muted-foreground mt-1">
              Make calls efficiently with our smart dialing system
            </p>
          </div>
          <div className="flex items-center space-x-2">
            <Badge variant="outline" className="gap-2">
              <div className="w-2 h-2 bg-success rounded-full animate-pulse"></div>
              Ready to Call
            </Badge>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Current Lead Info */}
          <Card className="lg:col-span-1">
            <CardHeader>
              <CardTitle>Current Lead</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <h3 className="font-semibold text-lg">{currentLead.name}</h3>
                <p className="text-muted-foreground">{currentLead.phone}</p>
                <p className="text-muted-foreground">{currentLead.email}</p>
              </div>
              
              <div className="flex items-center space-x-2">
                <Badge className="bg-destructive text-destructive-foreground">
                  {currentLead.status}
                </Badge>
                <span className="text-sm text-muted-foreground">{currentLead.lastContact}</span>
              </div>

              <div>
                <Label>Previous Notes</Label>
                <p className="text-sm text-muted-foreground mt-1">{currentLead.notes}</p>
              </div>

              <div className="space-y-2">
                <Label htmlFor="call-notes">Call Notes</Label>
                <Textarea 
                  id="call-notes"
                  placeholder="Add notes during the call..."
                  className="min-h-[100px]"
                />
              </div>
            </CardContent>
          </Card>

          {/* Dialer Interface */}
          <Card className="lg:col-span-2">
            <CardHeader>
              <CardTitle>Dialer Interface</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-6">
                {/* Phone Number Display */}
                <div className="text-center">
                  <Input
                    value={phoneNumber || currentLead.phone}
                    onChange={(e) => setPhoneNumber(e.target.value)}
                    className="text-xl text-center font-mono"
                    placeholder="Enter phone number"
                  />
                </div>

                {/* Call Status */}
                {isDialing && (
                  <div className="text-center py-4">
                    <div className="inline-flex items-center space-x-2 px-4 py-2 bg-success/10 rounded-full">
                      <div className="w-3 h-3 bg-success rounded-full animate-pulse"></div>
                      <span className="text-success font-medium">Call Active - {callDuration}</span>
                    </div>
                  </div>
                )}

                {/* Dialpad */}
                <div className="grid grid-cols-3 gap-3 max-w-xs mx-auto">
                  {dialpadNumbers.flat().map((number) => (
                    <Button
                      key={number}
                      variant="outline"
                      className="h-12 w-12 text-lg font-semibold"
                      onClick={() => handleDialpadClick(number)}
                    >
                      {number}
                    </Button>
                  ))}
                </div>

                {/* Call Controls */}
                <div className="flex justify-center space-x-4">
                  <Button
                    size="lg"
                    variant={isDialing ? "destructive" : "default"}
                    className="h-16 w-16 rounded-full"
                    onClick={handleCall}
                  >
                    {isDialing ? (
                      <PhoneOff className="h-8 w-8" />
                    ) : (
                      <Phone className="h-8 w-8" />
                    )}
                  </Button>
                  
                  {isDialing && (
                    <>
                      <Button
                        size="lg"
                        variant="outline"
                        className="h-16 w-16 rounded-full"
                        onClick={() => setIsMuted(!isMuted)}
                      >
                        {isMuted ? (
                          <MicOff className="h-6 w-6" />
                        ) : (
                          <Mic className="h-6 w-6" />
                        )}
                      </Button>
                      
                      <Button
                        size="lg"
                        variant="outline"
                        className="h-16 w-16 rounded-full"
                      >
                        <Volume2 className="h-6 w-6" />
                      </Button>
                    </>
                  )}
                </div>

                {/* Quick Actions */}
                <div className="grid grid-cols-2 gap-4">
                  <Button variant="outline" className="gap-2">
                    <PhoneCall className="h-4 w-4" />
                    Schedule Callback
                  </Button>
                  <Button variant="outline" className="gap-2">
                    <Pause className="h-4 w-4" />
                    Mark as No Answer
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Call Queue */}
        <Card>
          <CardHeader>
            <CardTitle>Call Queue</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {[
                { name: "Priya Sharma", phone: "+91 87654 32109", priority: "High" },
                { name: "Amit Patel", phone: "+91 76543 21098", priority: "Medium" },
                { name: "Sarah Johnson", phone: "+91 65432 10987", priority: "Low" }
              ].map((lead, index) => (
                <div key={index} className="flex items-center justify-between p-3 border rounded-lg">
                  <div className="flex items-center space-x-3">
                    <div className="w-8 h-8 bg-gradient-primary rounded-full flex items-center justify-center text-white text-sm font-medium">
                      {index + 2}
                    </div>
                    <div>
                      <p className="font-medium">{lead.name}</p>
                      <p className="text-sm text-muted-foreground">{lead.phone}</p>
                    </div>
                  </div>
                  <div className="flex items-center space-x-2">
                    <Badge variant={lead.priority === "High" ? "destructive" : lead.priority === "Medium" ? "secondary" : "outline"}>
                      {lead.priority}
                    </Badge>
                    <Button size="sm" variant="outline">
                      Call Now
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </Layout>
  );
}