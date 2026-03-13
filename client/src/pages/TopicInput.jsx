import { useState } from 'react';
import { generateQuiz } from '../services/api';

export default function TopicInput({ onGenerated, loading, setLoading, setError }) {
  const [topic, setTopic] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!topic.trim()) return;
    setError(null);
    setLoading(true);
    try {
      const quiz = await generateQuiz(topic);
      onGenerated(quiz);
    } catch (err) {
      setError(err.message || 'Failed to generate quiz');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card sm:p-8">
      <form onSubmit={handleSubmit} className="space-y-6">
        <div>
          <label htmlFor="topic" className="block text-sm font-semibold text-slate-700">
            Enter a topic
          </label>
          <p className="mt-1 text-sm text-slate-500">We&apos;ll generate 5 multiple-choice questions for you.</p>
        </div>
        <input
          id="topic"
          type="text"
          value={topic}
          onChange={(e) => setTopic(e.target.value)}
          placeholder="e.g. Photosynthesis, Neural Networks, Ancient Rome"
          className="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-4 py-3.5 text-slate-900 placeholder-slate-400 transition-colors focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:opacity-60"
          disabled={loading}
        />
        <button
          type="submit"
          disabled={loading || !topic.trim()}
          className="w-full rounded-xl bg-indigo-600 px-5 py-3.5 font-semibold text-white shadow-card transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 sm:w-auto sm:min-w-[180px]"
        >
          {loading ? 'Generating…' : 'Generate quiz'}
        </button>
      </form>
    </div>
  );
}
