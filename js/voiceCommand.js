// voiceCommand.js - Handles AI voice command functionality on dashboard.php

const voiceCommandButton = document.getElementById('voiceCommandButton');
const voiceStatus = document.getElementById('voiceStatus');
const entryForm = document.getElementById('entryForm');
const titleInput = document.getElementById('title');
const dateInput = document.getElementById('date');

let recognition;
let isListening = false;
let wakeWordDetected = false;
let audioChunks = [];
let mediaRecorder;

const wakeWord = 'har mahadev';

const languages = ['hi-IN', 'gu-IN', 'en-US'];

if (!voiceCommandButton || !voiceStatus || !entryForm || !titleInput || !dateInput) {
    console.error('Voice command elements missing');
} else if (!('webkitSpeechRecognition' in window || 'SpeechRecognition' in window)) {
    voiceStatus.textContent = 'Speech recognition is not supported in this browser.';
} else {
    voiceCommandButton.addEventListener('click', () => {
        if (isListening) {
            stopListening();
        } else {
            startWakeWordListening();
        }
    });
}
entryForm.addEventListener('submit', (e) => {
    e.preventDefault();
    fillAndSubmitEntryForm(titleInput.value, dateInput.value);
});

function startWakeWordListening() {
    voiceStatus.textContent = 'Listening for wake word: "Har Mahadev"...';
    isListening = true;
    wakeWordDetected = false;

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();

    recognition.continuous = true;
    recognition.interimResults = true;
    recognition.lang = 'en-US'; // Listen for wake word in English primarily

    recognition.onresult = (event) => {
        for (let i = event.resultIndex; i < event.results.length; ++i) {
            const transcript = event.results[i][0].transcript.toLowerCase();
            if (transcript.includes(wakeWord)) {
                wakeWordDetected = true;
                recognition.stop();
                voiceStatus.textContent = 'Wake word detected. Please speak your entry now.';
                startRecordingSpeech();
                break;
            }
        }
    };

    recognition.onerror = (event) => {
        console.error('Speech recognition error:', event.error);
        voiceStatus.textContent = 'Error during wake word detection: ' + event.error;
        stopListening();
    };

    recognition.onend = () => {
        if (!wakeWordDetected && isListening) {
            // Restart recognition to keep listening for wake word
            recognition.start();
        }
    };

    recognition.start();
}

function startRecordingSpeech() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        voiceStatus.textContent = 'Audio recording not supported in this browser.';
        stopListening();
        return;
    }

    navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];

        mediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                audioChunks.push(event.data);
            }
        };

        mediaRecorder.onstop = () => {
            const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
            sendAudioToServer(audioBlob);
        };

        voiceStatus.textContent = 'Recording your speech... Speak now.';
        mediaRecorder.start();

        // Automatically stop recording after max 10 seconds
        setTimeout(() => {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
                voiceStatus.textContent = 'Processing your speech...';
            }
        }, 10000);

    }).catch(err => {
        voiceStatus.textContent = 'Microphone access denied or error: ' + err.message;
        stopListening();
    });
}

function sendAudioToServer(audioBlob) {
    const formData = new FormData();
    formData.append('audio', audioBlob, 'voice_command.webm');

    fetch('api/voice_process.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
        }).then(response => response.json())
        .then(data => {
            if (data.error) {
                voiceStatus.textContent = 'Error in speech-to-text: ' + data.error;
                stopListening();
            } else if (data.text) {
                voiceStatus.textContent = 'Transcription received. Processing...';
                processNLU(data.text);
            } else {
                voiceStatus.textContent = 'Unexpected response from speech-to-text API.';
                stopListening();
            }
        }).catch(err => {
            voiceStatus.textContent = 'Network error during speech-to-text: ' + err.message;
            stopListening();
        });
}

function processNLU(transcribedText) {
    fetch('api/nlu_process.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ text: transcribedText })
        }).then(response => response.json())
        .then(data => {
            if (data.error) {
                voiceStatus.textContent = 'Error in NLU processing: ' + data.error;
                stopListening();
            } else {
                fillAndSubmitEntryForm(data.title, data.date);
            }
        }).catch(err => {
            voiceStatus.textContent = 'Network error during NLU processing: ' + err.message;
            stopListening();
        });
}

function fillAndSubmitEntryForm(title, date) {
    titleInput.value = title || '';
    dateInput.value = date || '';

    if (!titleInput.value) {
        voiceStatus.textContent = 'Could not extract title from voice input.';
        stopListening();
        return;
    }
    if (!dateInput.value) {
        voiceStatus.textContent = 'Could not extract date from voice input.';
        stopListening();
        return;
    }

    voiceStatus.textContent = 'Submitting entry...';

    const payload = {
        title: titleInput.value,
        date: dateInput.value
    };

    fetch('api/entries.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        }).then(response => response.json())
        .then(data => {
            if (data.error) {
                voiceStatus.textContent = 'Error adding entry: ' + (Array.isArray(data.error) ? data.error.join(', ') : data.error);
            } else if (data.success) {
                voiceStatus.textContent = 'Entry added successfully.';
                addEntryToList(data.entry);
                entryForm.reset();
            } else {
                voiceStatus.textContent = 'Unexpected response adding entry.';
            }
            stopListening();
        }).catch(err => {
            voiceStatus.textContent = 'Network error adding entry: ' + err.message;
            stopListening();
        });
}

function addEntryToList(entry) {
    const entriesList = document.getElementById('entriesList');
    if (!entriesList) return;

    // Remove "No entries found." message if present
    if (entriesList.children.length === 1 && entriesList.children[0].textContent === 'No entries found.') {
        entriesList.innerHTML = '';
    }

    const li = document.createElement('li');
    const strong = document.createElement('strong');
    strong.textContent = entry.title;
    const time = document.createElement('time');
    time.dateTime = entry.date;
    time.textContent = entry.date;
    li.appendChild(strong);
    li.appendChild(document.createTextNode(' '));
    li.appendChild(time);

    entriesList.insertBefore(li, entriesList.firstChild);
}

function stopListening() {
    if (recognition) {
        recognition.onresult = null;
        recognition.onerror = null;
        recognition.onend = null;
        try {
            recognition.stop();
        } catch {}
        recognition = null;
    }
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
    }
    isListening = false;
    wakeWordDetected = false;
    voiceStatus.textContent = '';
}