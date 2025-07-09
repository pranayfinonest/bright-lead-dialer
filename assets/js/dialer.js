// Dialer functionality for FINONEST TeleCRM

class TeleCRMDialer {
    constructor() {
        this.currentLeadIndex = 0;
        this.callQueue = [];
        this.isCallActive = false;
        this.callStartTime = null;
        this.callTimer = null;
        this.isOnBreak = false;
        this.autoDialing = false;
        this.isMuted = false;
        this.callDuration = 0;
        
        this.init();
    }
    
    init() {
        this.loadCallQueue();
        this.setupEventListeners();
        this.updateUI();
    }
    
    setupEventListeners() {
        // Call button
        const callBtn = document.getElementById('callBtn');
        if (callBtn) {
            callBtn.addEventListener('click', () => this.toggleCall());
        }
        
        // Dialpad buttons
        document.querySelectorAll('.dialpad-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.addToNumber(e.target.textContent);
            });
        });
        
        // Auto dial toggle
        const autoDialToggle = document.querySelector('.auto-dial-toggle');
        if (autoDialToggle) {
            autoDialToggle.addEventListener('click', () => this.toggleAutoDial());
        }
        
        // Break toggle
        const breakBtn = document.querySelector('[onclick="toggleBreak()"]');
        if (breakBtn) {
            breakBtn.addEventListener('click', () => this.toggleBreak());
        }
        
        // Skip lead button
        const skipBtn = document.querySelector('[onclick="skipLead()"]');
        if (skipBtn) {
            skipBtn.addEventListener('click', () => this.skipLead());
        }
        
        // Mute button
        const muteBtn = document.querySelector('[onclick="toggleMute()"]');
        if (muteBtn) {
            muteBtn.addEventListener('click', () => this.toggleMute());
        }
        
        // Disposition buttons
        document.querySelectorAll('.disposition-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const disposition = e.target.closest('.disposition-btn').getAttribute('onclick').match(/'([^']+)'/)[1];
                this.setDisposition(disposition);
            });
        });
    }
    
    loadCallQueue() {
        // Load call queue from server or use existing data
        if (typeof callQueue !== 'undefined') {
            this.callQueue = callQueue;
        }
    }
    
    toggleCall() {
        if (this.isOnBreak) {
            this.showAlert("You're on break. End break to start calling.");
            return;
        }
        
        if (!this.isCallActive) {
            this.startCall();
        } else {
            this.endCall();
        }
    }
    
    startCall() {
        const phoneNumber = document.getElementById('phoneNumber').value;
        if (!phoneNumber) {
            this.showAlert('Please enter a phone number');
            return;
        }
        
        this.isCallActive = true;
        this.callStartTime = new Date();
        this.callDuration = 0;
        
        // Update UI
        this.updateCallButton(true);
        this.showCallStatus();
        this.showSecondaryControls();
        
        // Start call timer
        this.startCallTimer();
        
        // Attempt native calling
        this.attemptNativeCall(phoneNumber);
        
        // Log call start
        this.logCallStart(phoneNumber);
        
        // Auto-end call after timeout (for demo)
        setTimeout(() => {
            if (this.isCallActive) {
                this.endCall();
            }
        }, 60000);
    }
    
    endCall() {
        this.isCallActive = false;
        
        // Update UI
        this.updateCallButton(false);
        this.hideCallStatus();
        this.hideSecondaryControls();
        
        // Stop timer
        this.stopCallTimer();
        
        // Show disposition modal
        this.showDispositionModal();
        
        // Log call end
        this.logCallEnd();
    }
    
    startCallTimer() {
        this.callTimer = setInterval(() => {
            if (this.callStartTime) {
                this.callDuration = Math.floor((new Date() - this.callStartTime) / 1000);
                this.updateCallDuration();
            }
        }, 1000);
    }
    
    stopCallTimer() {
        if (this.callTimer) {
            clearInterval(this.callTimer);
            this.callTimer = null;
        }
    }
    
    updateCallDuration() {
        const durationElement = document.getElementById('callDuration');
        if (durationElement) {
            const minutes = Math.floor(this.callDuration / 60).toString().padStart(2, '0');
            const seconds = (this.callDuration % 60).toString().padStart(2, '0');
            durationElement.textContent = `${minutes}:${seconds}`;
        }
    }
    
    updateCallButton(isActive) {
        const callBtn = document.getElementById('callBtn');
        if (callBtn) {
            if (isActive) {
                callBtn.innerHTML = '<i class="icon-phone-off"></i>';
                callBtn.className = 'call-btn error';
            } else {
                callBtn.innerHTML = '<i class="icon-phone"></i>';
                callBtn.className = 'call-btn primary';
            }
        }
    }
    
    showCallStatus() {
        const callStatus = document.getElementById('callStatus');
        if (callStatus) {
            callStatus.style.display = 'block';
        }
    }
    
    hideCallStatus() {
        const callStatus = document.getElementById('callStatus');
        if (callStatus) {
            callStatus.style.display = 'none';
        }
    }
    
    showSecondaryControls() {
        const controls = document.getElementById('secondaryControls');
        if (controls) {
            controls.style.display = 'flex';
        }
    }
    
    hideSecondaryControls() {
        const controls = document.getElementById('secondaryControls');
        if (controls) {
            controls.style.display = 'none';
        }
    }
    
    attemptNativeCall(phoneNumber) {
        // Try to use native calling if available
        if (window.location.protocol === 'https:' || window.location.hostname === 'localhost') {
            try {
                window.location.href = `tel:${phoneNumber}`;
            } catch (error) {
                console.log('Native calling not available:', error);
            }
        }
    }
    
    showDispositionModal() {
        const modal = document.getElementById('dispositionModal');
        if (modal) {
            modal.style.display = 'block';
        }
    }
    
    hideDispositionModal() {
        const modal = document.getElementById('dispositionModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    setDisposition(disposition) {
        // Save call record
        const callData = {
            lead_id: this.getCurrentLead()?.id,
            phone_number: document.getElementById('phoneNumber').value,
            disposition: disposition,
            notes: document.getElementById('callNotes').value,
            duration: this.callDuration
        };
        
        // Send to server
        this.saveCallRecord(callData);
        
        // Close modal and reset
        this.hideDispositionModal();
        this.clearCallNotes();
        
        // Move to next lead if auto-dialing
        if (this.autoDialing) {
            setTimeout(() => this.nextLead(), 1000);
        }
        
        this.showToast(`Call marked as: ${disposition.replace('_', ' ').toUpperCase()}`);
    }
    
    saveCallRecord(callData) {
        fetch('api/save_call.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(callData)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                console.log('Call record saved successfully');
            } else {
                console.error('Failed to save call record:', result.error);
            }
        })
        .catch(error => {
            console.error('Error saving call record:', error);
        });
    }
    
    nextLead() {
        if (this.currentLeadIndex < this.callQueue.length - 1) {
            this.currentLeadIndex++;
            this.updateCurrentLead();
        } else {
            this.showAlert('No more leads in queue');
        }
    }
    
    skipLead() {
        this.nextLead();
    }
    
    updateCurrentLead() {
        const currentLead = this.getCurrentLead();
        if (!currentLead) return;
        
        // Update lead index display
        const indexElement = document.getElementById('currentLeadIndex');
        if (indexElement) {
            indexElement.textContent = this.currentLeadIndex + 1;
        }
        
        // Update phone number
        const phoneInput = document.getElementById('phoneNumber');
        if (phoneInput) {
            phoneInput.value = currentLead.phone;
        }
        
        // Update lead display
        this.updateLeadDisplay(currentLead);
    }
    
    updateLeadDisplay(lead) {
        const leadInfo = document.getElementById('currentLeadInfo');
        if (leadInfo) {
            leadInfo.innerHTML = `
                <div class="lead-display">
                    <h3>${lead.name}</h3>
                    <p class="lead-phone">${lead.phone}</p>
                    <p class="lead-email">${lead.email}</p>
                </div>
                <div class="lead-status">
                    <span class="status-badge ${lead.status.toLowerCase()}">${lead.status}</span>
                </div>
                <div class="lead-notes">
                    <label>Previous Notes</label>
                    <div class="notes-display">${lead.notes || 'No previous notes'}</div>
                </div>
            `;
        }
    }
    
    getCurrentLead() {
        return this.callQueue[this.currentLeadIndex] || null;
    }
    
    toggleAutoDial() {
        this.autoDialing = !this.autoDialing;
        const btn = document.querySelector('.auto-dial-toggle');
        if (btn) {
            btn.textContent = this.autoDialing ? 'Auto ON' : 'Auto OFF';
            btn.className = this.autoDialing ? 'btn btn-primary auto-dial-toggle' : 'btn btn-outline auto-dial-toggle';
        }
        
        if (this.autoDialing && !this.isCallActive && !this.isOnBreak) {
            setTimeout(() => this.startCall(), 2000);
        }
    }
    
    toggleBreak() {
        this.isOnBreak = !this.isOnBreak;
        const btn = event.target;
        
        if (this.isOnBreak) {
            btn.innerHTML = '<i class="icon-coffee"></i> End Break';
            btn.className = 'btn btn-primary';
            this.autoDialing = false;
            this.updateAutoDialButton();
            this.showToast('Break started. Auto-dialing paused.');
        } else {
            btn.innerHTML = '<i class="icon-coffee"></i> Take Break';
            btn.className = 'btn btn-outline';
            this.showToast('Break ended. Ready to dial.');
        }
        
        this.updateStatusBadge();
    }
    
    updateAutoDialButton() {
        const btn = document.querySelector('.auto-dial-toggle');
        if (btn) {
            btn.textContent = 'Auto OFF';
            btn.className = 'btn btn-outline auto-dial-toggle';
        }
    }
    
    updateStatusBadge() {
        const badge = document.querySelector('.status-badge.ready');
        if (badge) {
            if (this.isOnBreak) {
                badge.innerHTML = '<div class="status-dot"></div> On Break';
                badge.className = 'status-badge warning';
            } else {
                badge.innerHTML = '<div class="status-dot"></div> Ready to Call';
                badge.className = 'status-badge ready';
            }
        }
    }
    
    toggleMute() {
        this.isMuted = !this.isMuted;
        const icon = document.getElementById('muteIcon');
        if (icon) {
            icon.className = this.isMuted ? 'icon-mic-off' : 'icon-mic';
        }
        
        this.showToast(this.isMuted ? 'Microphone muted' : 'Microphone unmuted');
    }
    
    addToNumber(digit) {
        const phoneInput = document.getElementById('phoneNumber');
        if (phoneInput) {
            phoneInput.value += digit;
        }
    }
    
    clearCallNotes() {
        const notesInput = document.getElementById('callNotes');
        if (notesInput) {
            notesInput.value = '';
        }
    }
    
    logCallStart(phoneNumber) {
        console.log(`Call started to ${phoneNumber} at ${new Date().toISOString()}`);
    }
    
    logCallEnd() {
        console.log(`Call ended after ${this.callDuration} seconds at ${new Date().toISOString()}`);
    }
    
    showAlert(message) {
        alert(message);
    }
    
    showToast(message) {
        if (window.TeleCRM && window.TeleCRM.showToast) {
            window.TeleCRM.showToast(message);
        } else {
            console.log(message);
        }
    }
    
    updateUI() {
        this.updateCurrentLead();
        this.updateStatusBadge();
    }
}

// Initialize dialer when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.dialer-container')) {
        window.dialer = new TeleCRMDialer();
    }
});

// Global functions for backward compatibility
function toggleCall() {
    if (window.dialer) {
        window.dialer.toggleCall();
    }
}

function setDisposition(disposition) {
    if (window.dialer) {
        window.dialer.setDisposition(disposition);
    }
}

function nextLead() {
    if (window.dialer) {
        window.dialer.nextLead();
    }
}

function skipLead() {
    if (window.dialer) {
        window.dialer.skipLead();
    }
}

function toggleAutoDial() {
    if (window.dialer) {
        window.dialer.toggleAutoDial();
    }
}

function toggleBreak() {
    if (window.dialer) {
        window.dialer.toggleBreak();
    }
}

function toggleMute() {
    if (window.dialer) {
        window.dialer.toggleMute();
    }
}

function addToNumber(digit) {
    if (window.dialer) {
        window.dialer.addToNumber(digit);
    }
}

function callNow(phoneNumber) {
    const phoneInput = document.getElementById('phoneNumber');
    if (phoneInput) {
        phoneInput.value = phoneNumber;
    }
    toggleCall();
}