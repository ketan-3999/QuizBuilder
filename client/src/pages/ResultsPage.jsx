export default function ResultsPage({ quiz, result, onStartOver }) {
  const pct = result.total > 0 ? Math.round((result.score / result.total) * 100) : 0;
  return (
    <div className="space-y-6">
      <div className="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card sm:p-8">
        <h2 className="text-sm font-semibold uppercase tracking-wider text-slate-500">Your score</h2>
        <p className="mt-2 text-3xl font-bold text-slate-900 sm:text-4xl">
          <span className="text-indigo-600">{result.score}</span>
          <span className="text-slate-400"> / </span>
          {result.total} correct
        </p>
        <div className="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-200">
          <div
            className="h-full rounded-full bg-indigo-600 transition-all duration-500"
            style={{ width: `${pct}%` }}
          />
        </div>
        <p className="mt-2 text-sm font-medium text-slate-600">{pct}%</p>
      </div>
      <div className="space-y-4">
        <h2 className="text-lg font-semibold text-slate-800">Review answers</h2>
        {quiz.questions.map((q, i) => {
          const detail = result.details[i] || {};
          const isCorrect = detail.isCorrect;
          return (
            <div
              key={i}
              className={`rounded-2xl border p-5 shadow-card transition-shadow ${
                isCorrect ? 'border-emerald-200/80 bg-emerald-50/60' : 'border-red-200/80 bg-red-50/60'
              }`}
            >
              <p className="font-medium leading-relaxed text-slate-900">{q.question}</p>
              <p className="mt-3 text-sm text-slate-600">
                <span className="font-medium text-emerald-700">Correct:</span>{' '}
                <strong className="text-slate-800">{q.options[detail.correctAnswer] ?? detail.correctAnswer}</strong>
                {!isCorrect && (
                  <>
                    {' · '}
                    <span className="font-medium text-red-700">Your answer:</span>{' '}
                    <strong className="text-slate-800">{q.options[detail.userAnswer] ?? detail.userAnswer}</strong>
                  </>
                )}
              </p>
            </div>
          );
        })}
      </div>
      <button
        type="button"
        onClick={onStartOver}
        className="w-full rounded-xl bg-indigo-600 px-5 py-3.5 font-semibold text-white shadow-card transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto sm:min-w-[200px]"
      >
        Start new quiz
      </button>
    </div>
  );
}
