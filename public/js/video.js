class VideoChat {
    constructor(peer) {
        this.peer = peer;
        this.init();
    }

    async init() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            document.getElementById('localVideo').srcObject = stream;

            this.peer.on('call', call => {
                call.answer(stream);
                call.on('stream', remoteStream => {
                    document.getElementById('remoteVideo').srcObject = remoteStream;
                });
                call.on('error', err => console.error('Call error:', err));
            });
        } catch (error) {
            console.error('Video chat init error:', error);
        }
    }

    call(peerId) {
        navigator.mediaDevices.getUserMedia({ video: true, audio: true })
            .then(stream => {
                const call = this.peer.call(peerId, stream);
                call.on('stream', remoteStream => {
                    document.getElementById('remoteVideo').srcObject = remoteStream;
                });
            })
            .catch(err => console.error('Call initiation error:', err));
    }
}

const videoChat = new VideoChat(app.peer);
