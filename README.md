
# About This Project

This web app is my first project. It’s a mini chat app with a UI similar to Telegram, though not as perfect. This project is built with Laravel version 11, PHP version 8.x, and Inertia Vue. If you want to test or run this project on your local machine, I can guide you step by step.






## Requirements

 - XAMPP for php 
 - Pusher for web socket
 - NPM 
 - Composer




## How to install in your local machine

At first, you need to clone this project or download this project to your local machine.




```bash
  git clone https://github.com/aungbhonemyat155/Chat-App-Laravel-Vue.git
```

After that, go to that project's directory and open command line or terminal and run this command:

```bash
  composer install
```

This will install all required packages as specified in the composer.json file.And another step is:

```bash
  cp .env.example .env
```

This command will create a new .env file by copying the .env.example file. After that:

```bash
  php artisan key:generate
```

This command will generate a new APP_KEY and add it to the .env file.

And configure you database in .env file, after that run these commands:

```bash
  php artisan migrate
```

```bash
  npm install
```

```bash
  npm run dev
```

And in another terminal, to serve laravel project run this command:

```bash
  php artisan serve
```

And in another terminal, to start the queue:work you need to run this command:

```bash
  php artisan queue:work
```

Afer all these steps, this project is ready to go!

