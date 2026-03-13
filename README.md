# AI-Powered Knowledge Quiz Builder

A minimal MVP that generates multiple-choice quizzes from a user-provided topic using an LLM, then lets users answer and see their score and the correct answers.

## Stack

- **Frontend:** React 18, Vite, Tailwind CSS
- **Backend:** PHP 7.3+ with MVC-style structure, JSON API only (compatible with older MAMP PHP 7.3)
- **AI:** Google Gemini API by default (e.g. `gemini-1.5-flash`); optional OpenAI via `AI_PROVIDER=openai`

## Architecture

- **PHP (MVC):** Single front controller (`api/public/index.php`) builds a request, dispatches by `operation_type` to `QuizController`, and sends the JSON response. The controller creates its own `QuizService` (via `AIServiceFactory`); no dependency injection. Models are `Quiz`, `Question`, and `QuizResult` (no database for MVP).
- **React:** Single-page flow: topic input → quiz (5 questions, 4 options A–D) → results (score + correct answers). The client calls the PHP API via `fetch`; in development, Vite proxies `/api` to the PHP backend.

## Design patterns & SOLID

- **Single Responsibility (SRP):** `Request` = input/path only; `ApiResponse` = response shape; `QuizController` = quiz operations; `OperationDispatcher` = routing; `index.php` = bootstrap and send only.
- **Open/Closed (OCP):** New AI providers: add a class implementing `AIServiceInterface` and extend `AIServiceFactory` (no change to controller or services). New operations: add a controller method and register it in `OperationDispatcher`.
- **Liskov Substitution:** Any `AIServiceInterface` implementation can be used where the interface is required.
- **Interface Segregation:** Small interface `AIServiceInterface` (single method); no fat interfaces.
- **Dependency Inversion (DIP):** `QuizService` depends on `AIServiceInterface`; `AIServiceFactory` creates the concrete implementation from config.
- **Front Controller:** Single entry point `index.php` that delegates to dispatcher and controller.
- **Factory:** `AIServiceFactory` creates the appropriate AI service from config.
- **Strategy:** Operation handlers are mapped by `operation_type`; new strategies can be added without modifying existing code.

## How to run

### Backend (PHP)

1. Copy env and set your Gemini API key (default provider):
   ```bash
   cd quiz-builder/api
   cp .env.example .env
   # Edit .env and set GEMINI_API_KEY=... (get from https://aistudio.google.com/app/apikey)
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Serve the API. For example, with MAMP, ensure the document root or a virtual host points to `quiz-builder/api/public`, so that requests like `http://localhost:8888/quiz-builder/api/public/api/quiz/generate` hit `index.php`. Or run PHP’s built-in server from the project root:
   ```bash
   cd quiz-builder/api/public
   php -S localhost:8888
   ```
   Then the API base is `http://localhost:8888` (and the front controller will see paths like `/api/quiz/generate`).

### Frontend (React)

1. Install and run:
   ```bash
   cd quiz-builder/client
   npm install
   npm run dev
   ```
2. Open the URL shown by Vite (e.g. `http://localhost:5173`). The dev server proxies `/api` to the PHP backend; if you use PHP’s built-in server as above, set the proxy target in `client/vite.config.js` to `http://localhost:8888` (or your PHP URL). If the API is under a path (e.g. MAMP), set `target` to that base (e.g. `http://localhost:8888/quiz-builder/api/public`).

### Production build

- Build the client: `cd client && npm run build`. Serve the `dist/` folder from your web server and point API requests to your PHP backend (same origin or configure CORS in PHP).

## AI tool and tradeoffs

- **Gemini** is the default provider (free tier available; get key at https://aistudio.google.com/app/apikey). Set `AI_PROVIDER=openai` and `OPENAI_API_KEY` in `.env` to use OpenAI instead. The prompt asks for exactly 5 questions in a fixed JSON schema. No retrieval or persistence in the MVP; the backend is stateless.

## Development tools

- **Cursor** was used as the IDE and AI pair-programming tool for implementation, refactoring, and documentation.

## Project layout

```
quiz-builder/
  api/
    public/index.php       # Front controller (bootstrap, dispatch, send)
    src/
      Controller/QuizController.php
      Core/OperationDispatcher.php
      Http/Request.php, ApiResponse.php
      Model/Quiz.php, Question.php, QuizResult.php
      Service/QuizService.php, AIServiceInterface.php, OpenAIService.php, GeminiService.php, AIServiceFactory.php
      Config/config.php
    .env.example
  client/
    src/
      pages/               # TopicInput, QuizPage, ResultsPage
      services/api.js
      App.jsx, main.jsx, index.css
    index.html, vite.config.js, tailwind.config.js
  README.md
```
