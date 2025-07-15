import React, { useState } from 'react';
import axios from 'axios';
import PlaylistResult from './PlaylistResult';

const MoodForm = () => {
  const [text, setText] = useState('');
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState(null);
  const [error, setError] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!text.trim()) return;

    setLoading(true);
    setError(null);

    try {
      const response = await axios.post('/api/analyze', { text });
      setResult(response.data);
    } catch (err) {
      setError('Failed to analyze your mood. Please try again.');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const moodEmojis = {
    happy: '😄',
    sad: '😢',
    calm: '😌',
    neutral: '😐'
  };

  return (
    <div className="mood-form-container">
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="mood-text">How are you feeling today?</label>
          <textarea
            id="mood-text"
            value={text}
            onChange={(e) => setText(e.target.value)}
            placeholder="Describe your mood (e.g., I feel excited about the new project!)"
            rows={4}
            disabled={loading}
          />
        </div>

        <button
          type="submit"
          className="analyze-btn"
          disabled={loading || !text.trim()}
        >
          {loading ? (
            <>
              <span className="spinner"></span> Analyzing...
            </>
          ) : 'Analyze Mood & Get Music'}
        </button>

        {error && <div className="error-message">{error}</div>}
      </form>

      {result && (
        <div className="result-container">
          <div className="mood-result">
            <h2>
              Your Mood:
              <span className={`mood-${result.mood}`}>
                {result.mood} {moodEmojis[result.mood] || '😐'}
              </span>
            </h2>
            <div className="sentiment-info">
              <div>
                <span>Polarity: </span>
                <span className={result.analysis.polarity > 0 ? 'positive' : result.analysis.polarity < 0 ? 'negative' : 'neutral'}>
                  {result.analysis.polarity.toFixed(2)}
                </span>
              </div>
              <div>
                <span>Subjectivity: </span>
                <span>{result.analysis.subjectivity.toFixed(2)}</span>
              </div>
            </div>
          </div>

          <PlaylistResult playlist={result.playlist} />
        </div>
      )}
    </div>
  );
};

export default MoodForm;
