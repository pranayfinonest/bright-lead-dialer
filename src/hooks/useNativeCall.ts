import { useState, useEffect } from 'react';
import { Device } from '@capacitor/device';
import { App } from '@capacitor/app';
import { Browser } from '@capacitor/browser';

export interface CallState {
  isActive: boolean;
  startTime: Date | null;
  endTime: Date | null;
  duration: number;
  phoneNumber: string;
  disposition: 'connected' | 'missed' | 'busy' | 'no_answer' | 'failed' | null;
}

export const useNativeCall = () => {
  const [callState, setCallState] = useState<CallState>({
    isActive: false,
    startTime: null,
    endTime: null,
    duration: 0,
    phoneNumber: '',
    disposition: null
  });

  const [isSupported, setIsSupported] = useState(false);

  useEffect(() => {
    checkNativeSupport();
    setupCallStateListener();
  }, []);

  const checkNativeSupport = async () => {
    try {
      const info = await Device.getInfo();
      setIsSupported(info.platform === 'android' || info.platform === 'ios');
    } catch (error) {
      console.log('Native features not available:', error);
      setIsSupported(false);
    }
  };

  const setupCallStateListener = () => {
    // Listen for app state changes to detect call completion
    App.addListener('appStateChange', ({ isActive }) => {
      if (isActive && callState.isActive) {
        // App became active again - call might have ended
        handleCallEnd();
      }
    });
  };

  const initiateCall = async (phoneNumber: string): Promise<boolean> => {
    try {
      setCallState(prev => ({
        ...prev,
        isActive: true,
        startTime: new Date(),
        phoneNumber,
        disposition: null
      }));

      if (isSupported) {
        // Use native dialer
        const telUrl = `tel:${phoneNumber.replace(/\s+/g, '')}`;
        await Browser.open({ 
          url: telUrl,
          presentationStyle: 'popover'
        });
        
        // Start call duration timer
        startCallTimer();
        return true;
      } else {
        // Fallback for web
        window.location.href = `tel:${phoneNumber}`;
        return false;
      }
    } catch (error) {
      console.error('Failed to initiate call:', error);
      setCallState(prev => ({
        ...prev,
        isActive: false,
        disposition: 'failed'
      }));
      return false;
    }
  };

  const startCallTimer = () => {
    const interval = setInterval(() => {
      setCallState(prev => {
        if (!prev.isActive || !prev.startTime) {
          clearInterval(interval);
          return prev;
        }
        
        const duration = Math.floor((Date.now() - prev.startTime.getTime()) / 1000);
        return { ...prev, duration };
      });
    }, 1000);

    // Auto-detect call end after reasonable time
    setTimeout(() => {
      if (callState.isActive) {
        handleCallEnd('no_answer');
      }
    }, 60000); // 1 minute timeout
  };

  const handleCallEnd = (disposition: CallState['disposition'] = 'connected') => {
    setCallState(prev => {
      if (!prev.isActive) return prev;
      
      const endTime = new Date();
      const finalDuration = prev.startTime 
        ? Math.floor((endTime.getTime() - prev.startTime.getTime()) / 1000)
        : 0;

      // Auto-determine disposition based on duration
      let autoDisposition = disposition;
      if (!disposition) {
        if (finalDuration < 5) {
          autoDisposition = 'no_answer';
        } else if (finalDuration < 15) {
          autoDisposition = 'busy';
        } else {
          autoDisposition = 'connected';
        }
      }

      return {
        ...prev,
        isActive: false,
        endTime,
        duration: finalDuration,
        disposition: autoDisposition
      };
    });
  };

  const resetCall = () => {
    setCallState({
      isActive: false,
      startTime: null,
      endTime: null,
      duration: 0,
      phoneNumber: '',
      disposition: null
    });
  };

  return {
    callState,
    isSupported,
    initiateCall,
    handleCallEnd,
    resetCall
  };
};