import { useState, useEffect, useRef } from 'react';
import { submitQuiz } from '../services/api';

const OPTIONS = ['A', 'B', 'C', 'D'];
const QUIZ_DURATION_SECONDS = 5 * 60; // 5 minutes

function formatTime(seconds) {
  const m = Math.floor(seconds / 60);
  const s = seconds % 60;
  return `${m}:${s.toString().padStart(2, '0')}`;
}

export default function QuizPage({ quiz, onSubmitted, onStartOver, loading, setLoading, setError }) {
  const [answers, setAnswers] = useState({});
  const [secondsLeft, setSecondsLeft] = useState(QUIZ_DURATION_SECONDS);
  const [timeUp, setTimeUp] = useState(false);
  const submittedByTimer = useRef(false);

  useEffect(() => {
    if (timeUp || loading) return;
    const id = setInterval(() => {
      setSecondsLeft((prev) => {
        if (prev <= 1) {
          clearInterval(id);
          setTimeUp(true);
          return 0;
        }
        return prev - 1;
      });
    }, 1000);
    return () => clearInterval(id);
  }, [timeUp, loading]);

  useEffect(() => {
    if (!timeUp || submittedByTimer.current) return;
    submittedByTimer.current = true;
    setError(null);
    setLoading(true);
    submitQuiz(quiz.id, answers)
      .then(onSubmitted)
      .catch((err) => setError(err.message || 'Failed to submit quiz'))
      .finally(() => setLoading(false));
  }, [timeUp]);

  const handleChange = (questionIndex, letter) => {
    if (timeUp) return;
    setAnswers((prev) => ({ ...prev, [questionIndex]: letter }));
  };

  const allAnswered = quiz.questions.length === Object.keys(answers).length;

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!allAnswered || loading || timeUp) return;
    setError(null);
    setLoading(true);
    try {
      const result = await submitQuiz(quiz.id, answers);
      onSubmitted(result);
    } catch (err) {
      setError(err.message || 'Failed to submit quiz');
    } finally {
      setLoading(false);
    }
  };

  const disabled = timeUp || loading;

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card">
        <p className="text-sm text-slate-600">
          Topic: <span className="font-semibold text-slate-800">{quiz.topic}</span>
        </p>
        <div
          className={`rounded-xl px-4 py-2 text-lg font-mono font-bold tabular-nums transition-colors ${
            secondsLeft <= 60 ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700'
          }`}
          aria-live="polite"
        >
          {timeUp ? '0:00' : formatTime(secondsLeft)}
        </div>
      </div>
      {timeUp && (
        <p className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800" role="alert">
          Time&apos;s up! Your answers have been submitted.
        </p>
      )}
      <form onSubmit={handleSubmit} className="space-y-6">
        {quiz.questions.map((q, i) => (
          <fieldset key={i} className="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card transition-shadow sm:p-6">
            <legend className="mb-3 text-xs font-semibold uppercase tracking-wider text-indigo-600">
              Question {i + 1} of {quiz.questions.length}
            </legend>
            <p className="mb-4 text-base font-medium leading-relaxed text-slate-900">{q.question}</p>
            <div className="space-y-2.5" role="radiogroup" aria-label={`Question ${i + 1} options`}>
              {OPTIONS.map((letter) => (
                <label
                  key={letter}
                  className="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 transition-all hover:border-slate-300 hover:bg-slate-50 has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50/80 has-[:checked]:ring-2 has-[:checked]:ring-indigo-500/20"
                >
                  <input
                    type="radio"
                    name={`q${i}`}
                    value={letter}
                    checked={answers[i] === letter}
                    onChange={() => handleChange(i, letter)}
                    disabled={disabled}
                    className="mt-0.5 h-4 w-4 shrink-0 border-slate-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50"
                  />
                  <span className="font-semibold text-slate-700">{letter}.</span>
                  <span className="text-slate-700">{q.options[letter]}</span>
                </label>
              ))}
            </div>
          </fieldset>
        ))}
        <div className="flex flex-wrap gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card sm:p-5">
          <button
            type="submit"
            disabled={!allAnswered || disabled}
            className="rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white shadow-card transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
          >
            {loading ? 'Submitting…' : 'Submit quiz'}
          </button>
          <button
            type="button"
            onClick={onStartOver}
            disabled={loading}
            className="rounded-xl border border-slate-300 bg-white px-5 py-3 font-medium text-slate-700 transition-colors hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 disabled:opacity-50"
          >
            Start over
          </button>
        </div>
      </form>
    </div>
  );
}
