import { useState } from 'react';
import TopicInput from './pages/TopicInput';
import QuizPage from './pages/QuizPage';
import ResultsPage from './pages/ResultsPage';

export default function App() {
  const [view, setView] = useState('topic'); // 'topic' | 'quiz' | 'results'
  const [quiz, setQuiz] = useState(null);
  const [result, setResult] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleGenerated = (generatedQuiz) => {
    setQuiz(generatedQuiz);
    setResult(null);
    setError(null);
    setView('quiz');
  };

  const handleSubmitted = (submitResult) => {
    setResult(submitResult);
    setView('results');
  };

  const handleStartOver = () => {
    setQuiz(null);
    setResult(null);
    setError(null);
    setView('topic');
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-slate-50 to-indigo-50/30 text-slate-900">
      <header className="sticky top-0 z-10 border-b border-slate-200/80 bg-white/90 backdrop-blur-sm shadow-card">
        <div className="mx-auto max-w-2xl px-4 py-5 sm:px-6">
          <h1 className="text-xl font-bold tracking-tight text-slate-800 sm:text-2xl">
            AI-Powered Knowledge Quiz Builder
          </h1>
          <p className="mt-0.5 text-sm text-slate-500">Generate and take quizzes on any topic</p>
        </div>
      </header>
      <main className="mx-auto max-w-2xl px-4 py-8 sm:px-6 sm:py-10">
        {error && (
          <div className="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 shadow-card" role="alert">
            {error}
          </div>
        )}
        {view === 'topic' && (
          <TopicInput
            onGenerated={handleGenerated}
            loading={loading}
            setLoading={setLoading}
            setError={setError}
          />
        )}
        {view === 'quiz' && quiz && (
          <QuizPage
            quiz={quiz}
            onSubmitted={handleSubmitted}
            onStartOver={handleStartOver}
            loading={loading}
            setLoading={setLoading}
            setError={setError}
          />
        )}
        {view === 'results' && result && quiz && (
          <ResultsPage
            quiz={quiz}
            result={result}
            onStartOver={handleStartOver}
          />
        )}
      </main>
    </div>
  );
}
