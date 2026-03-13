const BASE = import.meta.env.VITE_API_URL || '';

/** Get error message from API response (QueryMsg or fallback). */
function getMessage(data) {
  if (data && data.QueryMsg) return data.QueryMsg;
  return 'Request failed';
}

/**
 * @param {string} topic
 * @returns {Promise<{ id: string, topic: string, questions: Array<{ question: string, options: Record<string,string>, correct: string }>, createdAt: string }>}
 */
export async function generateQuiz(topic) {
  const res = await fetch(`${BASE}/api/quiz/generate`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ topic: topic.trim() }),
  });
  const data = await res.json();
  if (data && data.QueryCode === 200 && data.data != null) return data.data;
  throw new Error(getMessage(data));
}

/**
 * @param {string} quizId - quiz id as returned by generateQuiz (quiz.id)
 * @param {Record<number, string>} answers - question index -> selected letter (A-D)
 * @returns {Promise<{ score: number, total: number, details: Array<{ correctAnswer: string, userAnswer: string, isCorrect: boolean }> }>}
 */
export async function submitQuiz(quizId, answers) {
  const res = await fetch(`${BASE}/api/quiz/submit`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ quizId, answers }),
  });
  const data = await res.json();
  if (data && data.QueryCode === 200 && data.data != null) return data.data;
  throw new Error(getMessage(data));
}
