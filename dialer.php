<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

requireLogin();

$pageTitle = 'Auto Dialer - FINONEST TeleCRM';

// Get call queue
$stmt = $db->prepare("
    SELECT l.*, 
           COALESCE(c.last_call_time, 'Never') as last_contact,
           COALESCE(c.call_count, 0) as call_attempts
    FROM leads l
    LEFT JOIN (
        SELECT lead_id, 
               MAX(created_at) as last_call_time,
               COUNT(*) as call_count
        FROM calls 
        GROUP BY lead_id
    ) c ON l.id = c.lead_id
    WHERE l.status IN ('hot', 'warm', 'cold')
    ORDER BY 
        CASE l.status 
            WHEN 'hot' THEN 1 
            WHEN 'warm' THEN 2 
            WHEN 'cold' THEN 3 
        END,
        l.created_at ASC
    LIMIT 10
");
$stmt->execute();
$callQueue = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="dialer-container">
    <!-- Header -->
    <div class="page-header mobile-optimized">
        <div class="header-content">
            <div class="header-text desktop-only">
                <h1>Auto Dialer</h1>
                <p>Smart dialing with mobile optimization</p>
            </div>
            <div class="dialer-controls">
                <div class="status-badges">
                    <span class="status-badge ready">
                        <div class="status-dot"></div>
                        Ready to Call
                    </span>
                    <span class="status-badge native">
                        <i class="icon-smartphone"></i>
                        Native Calling
                    </span>
                </div>
                <button class="btn btn-outline auto-dial-toggle" onclick="toggleAutoDial()">
                    Auto OFF
                </button>
            </div>
        </div>
    </div>

    <div class="dialer-layout">
        <!-- Current Lead Info -->
        <div class="current-lead-card">
            <div class="card-header">
                <h3>
                    <i class="icon-user"></i>
                    Lead <span id="currentLeadIndex">1</span> of <?php echo count($callQueue); ?>
                </h3>
            </div>
            <div class="card-content">
                <div class="lead-info" id="currentLeadInfo">
                    <?php if (!empty($callQueue)): ?>
                        <div class="lead-display">
                            <h3><?php echo htmlspecialchars($callQueue[0]['name']); ?></h3>
                            <p class="lead-phone"><?php echo htmlspecialchars($callQueue[0]['phone']); ?></p>
                            <p class="lead-email"><?php echo htmlspecialchars($callQueue[0]['email']); ?></p>
                        </div>
                        
                        <div class="lead-status">
                            <span class="status-badge <?php echo strtolower($callQueue[0]['status']); ?>">
                                <?php echo ucfirst($callQueue[0]['status']); ?>
                            </span>
                        </div>

                        <div class="lead-notes">
                            <label>Previous Notes</label>
                            <div class="notes-display">
                                <?php echo htmlspecialchars($callQueue[0]['notes'] ?: 'No previous notes'); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-leads">
                            <p>No leads in queue</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="call-notes-section">
                    <label for="callNotes">Call Notes</label>
                    <textarea id="callNotes" placeholder="Add notes during the call..." rows="4"></textarea>
                </div>

                <button class="btn btn-outline full-width" onclick="skipLead()">
                    <i class="icon-skip-forward"></i>
                    Skip Lead
                </button>
            </div>
        </div>

        <!-- Dialer Interface -->
        <div class="dialer-interface-card">
            <div class="card-header">
                <h3>Dialer Controls</h3>
            </div>
            <div class="card-content">
                <div class="dialer-content">
                    <!-- Phone Number Display -->
                    <div class="phone-display">
                        <input type="tel" id="phoneNumber" class="phone-input" 
                               value="<?php echo !empty($callQueue) ? htmlspecialchars($callQueue[0]['phone']) : ''; ?>" 
                               placeholder="Enter phone number">
                    </div>

                    <!-- Call Status -->
                    <div class="call-status" id="callStatus" style="display: none;">
                        <div class="status-indicator active">
                            <div class="status-dot"></div>
                            <span>Call Active - <span id="callDuration">00:00</span></span>
                        </div>
                    </div>

                    <!-- Dialpad -->
                    <div class="dialpad">
                        <?php
                        $dialpadNumbers = [
                            ['1', '2', '3'],
                            ['4', '5', '6'],
                            ['7', '8', '9'],
                            ['*', '0', '#']
                        ];
                        ?>
                        <?php foreach ($dialpadNumbers as $row): ?>
                            <?php foreach ($row as $number): ?>
                                <button class="dialpad-btn" onclick="addToNumber('<?php echo $number; ?>')">
                                    <?php echo $number; ?>
                                </button>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- Call Controls -->
                    <div class="call-controls">
                        <button id="callBtn" class="call-btn primary" onclick="toggleCall()">
                            <i class="icon-phone"></i>
                        </button>
                        
                        <div class="secondary-controls" id="secondaryControls" style="display: none;">
                            <button class="control-btn" onclick="toggleMute()">
                                <i class="icon-mic" id="muteIcon"></i>
                            </button>
                            <button class="control-btn" onclick="toggleSpeaker()">
                                <i class="icon-volume-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Break Controls -->
                    <div class="break-controls">
                        <button class="btn btn-outline" onclick="toggleBreak()">
                            <i class="icon-coffee"></i>
                            Take Break
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call Queue -->
    <div class="call-queue-card">
        <div class="card-header">
            <h3>Call Queue</h3>
        </div>
        <div class="card-content">
            <div class="queue-list">
                <?php foreach (array_slice($callQueue, 1, 3) as $index => $lead): ?>
                    <div class="queue-item">
                        <div class="queue-number"><?php echo $index + 2; ?></div>
                        <div class="queue-info">
                            <p class="queue-name"><?php echo htmlspecialchars($lead['name']); ?></p>
                            <p class="queue-phone"><?php echo htmlspecialchars($lead['phone']); ?></p>
                        </div>
                        <div class="queue-priority">
                            <span class="priority-badge <?php echo strtolower($lead['status']); ?>">
                                <?php echo ucfirst($lead['status']); ?>
                            </span>
                        </div>
                        <button class="btn btn-outline btn-sm" onclick="callNow('<?php echo $lead['phone']; ?>')">
                            Call Now
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Disposition Modal -->
<div id="dispositionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Call Disposition</h3>
        </div>
        <div class="modal-body">
            <div class="disposition-grid">
                <button class="disposition-btn success" onclick="setDisposition('interested')">
                    <i class="icon-check-circle"></i>
                    <span>Interested</span>
                </button>
                <button class="disposition-btn error" onclick="setDisposition('not_interested')">
                    <i class="icon-x-circle"></i>
                    <span>Not Interested</span>
                </button>
                <button class="disposition-btn warning" onclick="setDisposition('callback')">
                    <i class="icon-clock"></i>
                    <span>Callback Required</span>
                </button>
                <button class="disposition-btn secondary" onclick="setDisposition('wrong_number')">
                    <i class="icon-phone"></i>
                    <span>Wrong Number</span>
                </button>
                <button class="disposition-btn secondary" onclick="setDisposition('no_answer')">
                    <i class="icon-phone-off"></i>
                    <span>No Answer</span>
                </button>
                <button class="disposition-btn warning" onclick="setDisposition('busy')">
                    <i class="icon-alert-circle"></i>
                    <span>Busy</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentLeadIndex = 0;
let callQueue = <?php echo json_encode($callQueue); ?>;
let isCallActive = false;
let callStartTime = null;
let callTimer = null;
let isOnBreak = false;
let autoDialing = false;
let isMuted = false;

function toggleCall() {
    if (isOnBreak) {
        alert("You're on break. End break to start calling.");
        return;
    }
    
    if (!isCallActive) {
        startCall();
    } else {
        endCall();
    }
}

function startCall() {
    const phoneNumber = document.getElementById('phoneNumber').value;
    if (!phoneNumber) {
        alert('Please enter a phone number');
        return;
    }
    
    isCallActive = true;
    callStartTime = new Date();
    
    // Update UI
    document.getElementById('callBtn').innerHTML = '<i class="icon-phone-off"></i>';
    document.getElementById('callBtn').className = 'call-btn error';
    document.getElementById('callStatus').style.display = 'block';
    document.getElementById('secondaryControls').style.display = 'flex';
    
    // Start call timer
    callTimer = setInterval(updateCallDuration, 1000);
    
    // Simulate call or integrate with actual calling service
    if (window.location.protocol === 'https:' || window.location.hostname === 'localhost') {
        // Try to use native calling if available
        window.location.href = `tel:${phoneNumber}`;
    }
    
    // Auto-end call after 60 seconds for demo
    setTimeout(() => {
        if (isCallActive) {
            endCall();
        }
    }, 60000);
}

function endCall() {
    isCallActive = false;
    
    // Update UI
    document.getElementById('callBtn').innerHTML = '<i class="icon-phone"></i>';
    document.getElementById('callBtn').className = 'call-btn primary';
    document.getElementById('callStatus').style.display = 'none';
    document.getElementById('secondaryControls').style.display = 'none';
    
    // Stop timer
    if (callTimer) {
        clearInterval(callTimer);
        callTimer = null;
    }
    
    // Show disposition modal
    document.getElementById('dispositionModal').style.display = 'block';
}

function updateCallDuration() {
    if (callStartTime) {
        const duration = Math.floor((new Date() - callStartTime) / 1000);
        const minutes = Math.floor(duration / 60).toString().padStart(2, '0');
        const seconds = (duration % 60).toString().padStart(2, '0');
        document.getElementById('callDuration').textContent = `${minutes}:${seconds}`;
    }
}

function setDisposition(disposition) {
    // Save call record
    const callData = {
        lead_id: callQueue[currentLeadIndex]?.id,
        phone_number: document.getElementById('phoneNumber').value,
        disposition: disposition,
        notes: document.getElementById('callNotes').value,
        duration: callStartTime ? Math.floor((new Date() - callStartTime) / 1000) : 0
    };
    
    // Send to server
    fetch('api/save_call.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(callData)
    });
    
    // Close modal and move to next lead
    document.getElementById('dispositionModal').style.display = 'none';
    document.getElementById('callNotes').value = '';
    
    if (autoDialing) {
        setTimeout(nextLead, 1000);
    }
}

function nextLead() {
    if (currentLeadIndex < callQueue.length - 1) {
        currentLeadIndex++;
        updateCurrentLead();
    }
}

function skipLead() {
    nextLead();
}

function updateCurrentLead() {
    if (callQueue[currentLeadIndex]) {
        const lead = callQueue[currentLeadIndex];
        document.getElementById('currentLeadIndex').textContent = currentLeadIndex + 1;
        document.getElementById('phoneNumber').value = lead.phone;
        
        // Update lead display
        const leadInfo = document.getElementById('currentLeadInfo');
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

function toggleAutoDial() {
    autoDialing = !autoDialing;
    const btn = document.querySelector('.auto-dial-toggle');
    btn.textContent = autoDialing ? 'Auto ON' : 'Auto OFF';
    btn.className = autoDialing ? 'btn btn-primary auto-dial-toggle' : 'btn btn-outline auto-dial-toggle';
}

function toggleBreak() {
    isOnBreak = !isOnBreak;
    const btn = event.target;
    if (isOnBreak) {
        btn.innerHTML = '<i class="icon-coffee"></i> End Break';
        btn.className = 'btn btn-primary';
        autoDialing = false;
        document.querySelector('.auto-dial-toggle').textContent = 'Auto OFF';
    } else {
        btn.innerHTML = '<i class="icon-coffee"></i> Take Break';
        btn.className = 'btn btn-outline';
    }
}

function toggleMute() {
    isMuted = !isMuted;
    const icon = document.getElementById('muteIcon');
    icon.className = isMuted ? 'icon-mic-off' : 'icon-mic';
}

function addToNumber(digit) {
    const phoneInput = document.getElementById('phoneNumber');
    phoneInput.value += digit;
}

function callNow(phoneNumber) {
    document.getElementById('phoneNumber').value = phoneNumber;
    toggleCall();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('dispositionModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include 'includes/footer.php'; ?>