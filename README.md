# SkillSwap
A platform for skill exchange through virtual bartering.

## Author
Rick Hayes

## License
MIT License

## Version
1.1

## Description
SkillSwap connects people who want to learn new skills with those willing to teach them through a virtual barter system. Built with JavaScript, PHP, MySQL, and Apache.

## Features
- Skill matching algorithm
- Virtual classroom with WebRTC
- Rating system
- Progress tracking with gamification
- File sharing
- Monetization (Freemium, Subscription, Transaction fees)

## Requirements
- Apache 2.4+
- PHP 7.4+
- MySQL 5.7+
- Composer
- Node.js & npm

## Installation
1. Clone the repository
2. Install PHP dependencies: `composer install`
3. Install JS dependencies: `cd public && npm install`
4. Configure database in `api/config.php`
5. Set up Stripe keys in `api/payments.php`
6. Start Apache server

## Usage
- Register/login
- Add skills you offer/want
- Get matched with users
- Schedule sessions
- Track progress and earn badges

## Contributing
Pull requests welcome. Please follow the coding standards and include tests.
