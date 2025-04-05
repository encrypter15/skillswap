class SkillSwap {
    constructor() {
        this.peer = new Peer();
        this.stripe = Stripe('your_stripe_public_key');
        this.token = localStorage.getItem('token');
        this.initEventListeners();
        this.loadProfile();
    }

    initEventListeners() {
        document.getElementById('profileBtn').addEventListener('click', () => this.showSection('profile'));
        document.getElementById('matchesBtn').addEventListener('click', () => this.loadMatches());
        document.getElementById('sessionsBtn').addEventListener('click', () => this.loadSessions());
        document.getElementById('progressBtn').addEventListener('click', () => this.loadProgress());
        document.getElementById('addSkillForm').addEventListener('submit', (e) => this.addSkill(e));
    }

    async fetchWithAuth(url, options = {}) {
        options.headers = {
            ...options.headers,
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'application/json'
        };
        const response = await fetch(url, options);
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    }

    showSection(sectionId) {
        document.querySelectorAll('.section').forEach(section => {
            section.classList.add('hidden');
        });
        document.getElementById(sectionId).classList.remove('hidden');
    }

    async loadProfile() {
        try {
            const skills = await this.fetchWithAuth('/api/skills.php?user_id=1'); // Replace with actual user_id from token
            this.renderSkills(skills);
        } catch (error) {
            console.error('Error loading profile:', error);
        }
    }

    renderSkills(skills) {
        const offered = skills.filter(s => s.type === 'offer');
        const wanted = skills.filter(s => s.type === 'want');
        document.getElementById('offered-skills').innerHTML = offered.map(s => `<div>${s.skill_name} (${s.proficiency}%)</div>`).join('');
        document.getElementById('wanted-skills').innerHTML = wanted.map(s => `<div>${s.skill_name}</div>`).join('');
    }

    async addSkill(e) {
        e.preventDefault();
        const skillName = document.getElementById('skillName').value;
        const skillType = document.getElementById('skillType').value;
        const proficiency = document.getElementById('proficiency').value || 0;
        
        try {
            await this.fetchWithAuth('/api/skills.php', {
                method: 'POST',
                body: JSON.stringify({ user_id: 1, skill_name: skillName, type: skillType, proficiency }) // Replace with actual user_id
            });
            this.loadProfile();
        } catch (error) {
            console.error('Error adding skill:', error);
        }
    }

    async loadProgress() {
        try {
            const progress = await this.fetchWithAuth('/api/progress.php?user_id=1'); // Replace with actual user_id
            this.renderProgressChart(progress);
            this.showSection('progress');
        } catch (error) {
            console.error('Error loading progress:', error);
        }
    }

    renderProgressChart(progress) {
        const ctx = document.getElementById('progressChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: progress.map(p => p.skill_name),
                datasets: [{
                    label: 'Progress Level',
                    data: progress.map(p => p.progress_level),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true, max: 100 }
                }
            }
        });
    }

    async handleSubscription() {
        try {
            const response = await this.fetchWithAuth('/api/payments.php', {
                method: 'POST',
                body: JSON.stringify({ user_id: 1 }) // Replace with actual user_id
            });
            const { sessionId } = response;
            this.stripe.redirectToCheckout({ sessionId });
        } catch (error) {
            console.error('Error creating subscription:', error);
        }
    }
}

const app = new SkillSwap();
