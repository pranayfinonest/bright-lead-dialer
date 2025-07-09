import { useState, useEffect } from "react";
import { Phone, PhoneCall, PhoneOff, Pause, Play, Volume2, Mic, MicOff, Coffee, SkipForward, Clock, User, CheckCircle, XCircle, AlertCircle, Smartphone } from "lucide-react";
import { useNativeCall } from "@/hooks/useNativeCall";
import Layout from "@/components/layout/Layout";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { toast } from "sonner";

export default function Dialer() {
  const { callState, isSupported, initiateCall, handleCallEnd, resetCall } = useNativeCall();
  const [isMuted, setIsMuted] = useState(false);
  const [isOnBreak, setIsOnBreak] = useState(false);
  const [autoDialing, setAutoDialing] = useState(false);
  const [phoneNumber, setPhoneNumber] = useState("");
  const [currentLeadIndex, setCurrentLeadIndex] = useState(0);
  const [callNotes, setCallNotes] = useState("");
  const [showDisposition, setShowDisposition] = useState(false);

  const callQueue = [
    { name: "Rajesh Kumar", phone: "+91 98765 43210", email: "rajesh@email.com", status: "Hot", notes: "Interested in home loan" },
    { name: "Priya Sharma", phone: "+91 87654 32109", email: "priya@email.com", status: "Warm", notes: "Looking for personal loan" },
    { name: "Amit Patel", phone: "+91 76543 21098", email: "amit@email.com", status: "Cold", notes: "Credit card inquiry" },
  ];

  const currentLead = callQueue[currentLeadIndex];

  const dispositions = [
    { id: 'interested', label: 'Interested', color: 'bg-success', icon: CheckCircle },
    { id: 'not_interested', label: 'Not Interested', color: 'bg-destructive', icon: XCircle },
    { id: 'callback', label: 'Callback Required', color: 'bg-warning', icon: Clock },
    { id: 'wrong_number', label: 'Wrong Number', color: 'bg-muted', icon: Phone },
    { id: 'no_answer', label: 'No Answer', color: 'bg-secondary', icon: PhoneOff },
    { id: 'busy', label: 'Busy', color: 'bg-orange-500', icon: AlertCircle },
  ];

  const dialpadNumbers = [
    ['1', '2', '3'],
    ['4', '5', '6'],
    ['7', '8', '9'],
    ['*', '0', '#']
  ];

  // Auto-dialing effect
  useEffect(() => {
    if (autoDialing && !callState.isActive && !isOnBreak && !showDisposition) {
      const timer = setTimeout(() => {
        handleCall();
      }, 2000);
      return () => clearTimeout(timer);
    }
  }, [autoDialing, callState.isActive, isOnBreak, showDisposition, currentLeadIndex]);

  // Auto-disposition when call ends
  useEffect(() => {
    if (callState.disposition && !showDisposition) {
      setShowDisposition(true);
    }
  }, [callState.disposition, showDisposition]);

  const handleDialpadClick = (number: string) => {
    setPhoneNumber(prev => prev + number);
  };

  const handleCall = async () => {
    if (isOnBreak) {
      toast.error("You're on break. End break to start calling.");
      return;
    }
    
    if (!callState.isActive) {
      const number = phoneNumber || currentLead.phone;
      toast.success(`${isSupported ? 'Calling' : 'Dialing'} ${currentLead.name}...`);
      
      const success = await initiateCall(number);
      if (!success && !isSupported) {
        // For web fallback, simulate call state
        setTimeout(() => setShowDisposition(true), 3000);
      }
    } else {
      // End call
      handleCallEnd();
      toast.info("Call ended");
    }
  };

  const handleNextLead = () => {
    setCurrentLeadIndex((prev) => (prev + 1) % callQueue.length);
    setCallNotes("");
    setShowDisposition(false);
    toast.info("Moving to next lead");
  };

  const handleDisposition = (disposition: any) => {
    toast.success(`Call marked as: ${disposition.label}`);
    setShowDisposition(false);
    setCallNotes("");
    resetCall();
    if (autoDialing) {
      setTimeout(() => handleNextLead(), 1000);
    }
  };

  const toggleBreak = () => {
    setIsOnBreak(!isOnBreak);
    if (!isOnBreak) {
      setAutoDialing(false);
      toast.info("Break started. Auto-dialing paused.");
    } else {
      toast.info("Break ended. Ready to dial.");
    }
  };

  return (
    <Layout>
      <div className="space-y-6">
        {/* Mobile-Optimized Header */}
        <div className="flex flex-col space-y-3 md:space-y-0 md:flex-row md:items-center justify-between">
          <div className="hidden md:block">
            <h1 className="text-2xl md:text-3xl font-bold text-foreground">Auto Dialer</h1>
            <p className="text-muted-foreground mt-1 text-sm">
              Smart dialing with mobile optimization
            </p>
          </div>
          <div className="flex flex-wrap items-center gap-2">
            <Badge variant="outline" className={`gap-2 text-xs ${isOnBreak ? 'bg-warning/10' : 'bg-success/10'}`}>
              <div className={`w-2 h-2 rounded-full animate-pulse ${isOnBreak ? 'bg-warning' : 'bg-success'}`}></div>
              {isOnBreak ? 'On Break' : 'Ready to Call'}
            </Badge>
            {isSupported && (
              <Badge variant="outline" className="gap-2 text-xs bg-primary/10">
                <Smartphone className="w-3 h-3" />
                Native Calling
              </Badge>
            )}
            <Button
              size="sm"
              variant={autoDialing ? "default" : "outline"}
              onClick={() => setAutoDialing(!autoDialing)}
              disabled={isOnBreak}
              className="text-xs"
            >
              {autoDialing ? "Auto ON" : "Auto OFF"}
            </Button>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
          {/* Mobile-Optimized Current Lead Info */}
          <Card className="lg:col-span-1">
            <CardHeader className="pb-3">
              <CardTitle className="flex items-center gap-2 text-lg">
                <User className="h-5 w-5" />
                Lead {currentLeadIndex + 1} of {callQueue.length}
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="text-center p-4 bg-gradient-primary/10 rounded-lg">
                <h3 className="font-semibold text-lg">{currentLead.name}</h3>
                <p className="text-muted-foreground text-sm">{currentLead.phone}</p>
                <p className="text-muted-foreground text-xs">{currentLead.email}</p>
              </div>
              
              <div className="flex items-center justify-center">
                <Badge className="bg-destructive text-destructive-foreground">
                  {currentLead.status}
                </Badge>
              </div>

              <div>
                <Label className="text-sm">Previous Notes</Label>
                <p className="text-sm text-muted-foreground mt-1 p-2 bg-muted/30 rounded">
                  {currentLead.notes}
                </p>
              </div>

              <div className="space-y-2">
                <Label htmlFor="call-notes" className="text-sm">Call Notes</Label>
                <Textarea 
                  id="call-notes"
                  placeholder="Add notes during the call..."
                  className="min-h-[80px] text-sm"
                  value={callNotes}
                  onChange={(e) => setCallNotes(e.target.value)}
                />
              </div>

              <Button 
                variant="outline" 
                className="w-full gap-2" 
                onClick={handleNextLead}
                disabled={callState.isActive}
              >
                <SkipForward className="h-4 w-4" />
                Skip Lead
              </Button>
            </CardContent>
          </Card>

          {/* Mobile-Optimized Dialer Interface */}
          <Card className="lg:col-span-2">
            <CardHeader className="pb-3">
              <CardTitle className="text-lg">Dialer Controls</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {/* Phone Number Display */}
                <div className="text-center">
                  <Input
                    value={phoneNumber || currentLead.phone}
                    onChange={(e) => setPhoneNumber(e.target.value)}
                    className="text-lg md:text-xl text-center font-mono h-12"
                    placeholder="Enter phone number"
                  />
                </div>

                {/* Call Status */}
                {callState.isActive && (
                  <div className="text-center py-3">
                    <div className="inline-flex items-center space-x-2 px-4 py-2 bg-success/10 rounded-full">
                      <div className="w-3 h-3 bg-success rounded-full animate-pulse"></div>
                      <span className="text-success font-medium">
                        Call Active - {Math.floor(callState.duration / 60).toString().padStart(2, '0')}:
                        {(callState.duration % 60).toString().padStart(2, '0')}
                      </span>
                    </div>
                  </div>
                )}

                {/* Call State Info */}
                {callState.disposition && !callState.isActive && (
                  <div className="text-center py-2">
                    <Badge variant="secondary" className="text-xs">
                      Last Call: {callState.disposition.replace('_', ' ').toUpperCase()}
                    </Badge>
                  </div>
                )}

                {/* Mobile-Optimized Dialpad */}
                <div className="grid grid-cols-3 gap-2 max-w-xs mx-auto">
                  {dialpadNumbers.flat().map((number) => (
                    <Button
                      key={number}
                      variant="outline"
                      className="h-12 w-full text-lg font-semibold touch-manipulation"
                      onClick={() => handleDialpadClick(number)}
                    >
                      {number}
                    </Button>
                  ))}
                </div>

                {/* Mobile-Optimized Call Controls */}
                <div className="flex justify-center items-center space-x-3">
                  <Button
                    size="lg"
                    variant={callState.isActive ? "destructive" : "call"}
                    className="h-16 w-16 rounded-full touch-manipulation"
                    onClick={handleCall}
                    disabled={isOnBreak}
                  >
                    {callState.isActive ? (
                      <PhoneOff className="h-8 w-8" />
                    ) : (
                      <Phone className="h-8 w-8" />
                    )}
                  </Button>
                  
                  {callState.isActive && (
                    <>
                      <Button
                        size="lg"
                        variant="outline"
                        className="h-14 w-14 rounded-full touch-manipulation"
                        onClick={() => setIsMuted(!isMuted)}
                      >
                        {isMuted ? (
                          <MicOff className="h-5 w-5" />
                        ) : (
                          <Mic className="h-5 w-5" />
                        )}
                      </Button>
                      
                      <Button
                        size="lg"
                        variant="outline"
                        className="h-14 w-14 rounded-full touch-manipulation"
                      >
                        <Volume2 className="h-5 w-5" />
                      </Button>
                    </>
                  )}
                </div>

                {/* Break Controls */}
                <div className="flex justify-center">
                  <Button
                    variant={isOnBreak ? "default" : "outline"}
                    className="gap-2 touch-manipulation"
                    onClick={toggleBreak}
                  >
                    <Coffee className="h-4 w-4" />
                    {isOnBreak ? "End Break" : "Take Break"}
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Disposition Dialog */}
        <Dialog open={showDisposition} onOpenChange={setShowDisposition}>
          <DialogContent className="max-w-md">
            <DialogHeader>
              <DialogTitle>Call Disposition</DialogTitle>
            </DialogHeader>
            <div className="grid grid-cols-2 gap-3">
              {dispositions.map((disposition) => (
                <Button
                  key={disposition.id}
                  variant="outline"
                  className={`h-16 flex flex-col gap-1 touch-manipulation ${disposition.color} text-white border-0`}
                  onClick={() => handleDisposition(disposition)}
                >
                  <disposition.icon className="h-5 w-5" />
                  <span className="text-xs">{disposition.label}</span>
                </Button>
              ))}
            </div>
          </DialogContent>
        </Dialog>

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