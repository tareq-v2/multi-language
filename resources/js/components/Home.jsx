import React, { useState, useEffect, useRef } from 'react';
import axios from 'axios';
import './playlists/index.css';

function Home() {
  const [text, setText] = useState('');
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState(null);
  const [error, setError] = useState(null);
  const [isTyping, setIsTyping] = useState(false);
  const [charCount, setCharCount] = useState(0);
  const textareaRef = useRef(null);
  const [suggestions, setSuggestions] = useState([]);

  // Mood emojis with color coding
  const moodEmojis = {
    happy: { emoji: '😄', color: '#FFD700' },
    excited: { emoji: '🤩', color: '#FF6B6B' },
    sad: { emoji: '😢', color: '#4D96FF' },
    calm: { emoji: '😌', color: '#6BCB77' },
    neutral: { emoji: '😐', color: '#9BA3EB' },
    angry: { emoji: '😠', color: '#FF6B6B' },
    anxious: { emoji: '😰', color: '#FFA559' },
    romantic: { emoji: '🥰', color: '#FF78C4' },
    energetic: { emoji: '💥', color: '#FF9F29' },
    nostalgic: { emoji: '🕰️', color: '#A7D2CB' }
  };

  // Sample mood suggestions
  const moodSuggestions = [
    "I'm feeling really excited about my upcoming vacation!",
    "Just had a rough day at work and feeling pretty down...",
    "Feeling calm and relaxed after my morning meditation",
    "Not sure how I feel today, just neutral I guess",
    "I'm so angry about what happened this morning!",
    "Feeling anxious about my job interview tomorrow",
    "Just had the most romantic dinner with my partner",
    "Full of energy after my workout this morning!",
    "Listening to old songs and feeling nostalgic"
  ];

  // Set a random suggestion initially
  useEffect(() => {
    const randomSuggestion = moodSuggestions[Math.floor(Math.random() * moodSuggestions.length)];
    setSuggestions([randomSuggestion]);
  }, []);

  // Rotate suggestions every 5 seconds
  useEffect(() => {
    if (!isTyping && !text) {
      const interval = setInterval(() => {
        setSuggestions(prev => {
          const usedSuggestions = [...prev];
          const available = moodSuggestions.filter(s => !usedSuggestions.includes(s));

          if (available.length === 0) {
            return [moodSuggestions[Math.floor(Math.random() * moodSuggestions.length)]];
          }

          const randomSuggestion = available[Math.floor(Math.random() * available.length)];
          return [...prev.slice(1), randomSuggestion];
        });
      }, 5000);

      return () => clearInterval(interval);
    }
  }, [isTyping, text]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!text.trim()) return;

    setLoading(true);
    setError(null);

    try {
      // Actual API call to your backend
      const response = await axios.post('/mood/analyze', { text });
      setResult(response.data);
    } catch (err) {
      setError('Failed to analyze your mood. Please try again.');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleTextChange = (e) => {
    const value = e.target.value;
    setText(value);
    setCharCount(value.length);
    setIsTyping(true);
  };

  const handleReset = () => {
    setText('');
    setResult(null);
    setError(null);
    setCharCount(0);
    if (textareaRef.current) {
      textareaRef.current.focus();
    }
  };

  const handleSuggestionClick = (suggestion) => {
    setText(suggestion);
    setCharCount(suggestion.length);
  };

  const getMoodColor = (mood) => {
    return moodEmojis[mood]?.color || '#9BA3EB';
  };

  return (
    <div className="app">
      <div className="header">
        <div className="logo-container">
          <div className="logo">🎵</div>
          <h1>MoodTunes</h1>
        </div>
        <p className="tagline">Share your feelings, discover your perfect soundtrack</p>
      </div>

      <div className="mood-form-container">
        <div className="form-card">
          <form onSubmit={handleSubmit}>
            <div className="form-group">
              <label htmlFor="mood-text">
                <div className="label-content">
                  <span className="pulse-icon">💬</span>
                  <span>How are you feeling today?</span>
                </div>
              </label>

              <textarea
                ref={textareaRef}
                id="mood-text"
                value={text}
                onChange={handleTextChange}
                onFocus={() => setIsTyping(true)}
                onBlur={() => setTimeout(() => setIsTyping(false), 200)}
                placeholder="Describe your mood (e.g., I feel excited about the new project!)"
                rows={4}
                disabled={loading}
                maxLength={300}
              />

              <div className="textarea-footer">
                <div className="char-counter">{charCount}/300</div>
                {!text && (
                  <div className="suggestions">
                    <span>Try: </span>
                    {suggestions.map((suggestion, index) => (
                      <button
                        key={index}
                        type="button"
                        className="suggestion-btn"
                        onClick={() => handleSuggestionClick(suggestion)}
                      >
                        {suggestion}
                      </button>
                    ))}
                  </div>
                )}
              </div>
            </div>

            <div className="button-group">
              <button
                type="submit"
                className="analyze-btn"
                disabled={loading || !text.trim()}
              >
                {loading ? (
                  <>
                    <span className="spinner"></span> Analyzing your mood...
                  </>
                ) : (
                  <>
                    <span className="icon">🔍</span> Analyze Mood & Get Music
                  </>
                )}
              </button>

              <button
                type="button"
                className="reset-btn"
                onClick={handleReset}
                disabled={loading}
              >
                <span className="icon">🔄</span> Reset
              </button>
            </div>

            {error && <div className="error-message">{error}</div>}
          </form>
        </div>
      </div>

      {result && (
        <div className="result-container slide-in">
          <div className="mood-result" style={{ backgroundColor: `${getMoodColor(result.mood)}22` }}>
            <div className="mood-header">
              <div className="mood-icon" style={{ backgroundColor: getMoodColor(result.mood) }}>
                {moodEmojis[result.mood]?.emoji || '😐'}
              </div>
              <h2>
                Your Mood: <span className={`mood-${result.mood}`}>{result.mood}</span>
              </h2>
            </div>

            <div className="sentiment-info">
              <div className="sentiment-card">
                <div className="sentiment-label">Polarity</div>
                <div className={`sentiment-value ${result.analysis.polarity > 0 ? 'positive' : result.analysis.polarity < 0 ? 'negative' : 'neutral'}`}>
                  {result.analysis.polarity}
                </div>
                <div className="sentiment-bar">
                  <div
                    className="bar-fill"
                    style={{
                      width: `${(parseFloat(result.analysis.polarity) + 1) * 50}%`,
                      backgroundColor: result.analysis.polarity > 0 ? '#6BCB77' : result.analysis.polarity < 0 ? '#FF6B6B' : '#9BA3EB'
                    }}
                  ></div>
                </div>
              </div>

              <div className="sentiment-card">
                <div className="sentiment-label">Subjectivity</div>
                <div className="sentiment-value">{result.analysis.subjectivity}</div>
                <div className="sentiment-bar">
                  <div
                    className="bar-fill"
                    style={{
                      width: `${parseFloat(result.analysis.subjectivity) * 100}%`,
                      backgroundColor: '#FFD700'
                    }}
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <div className="playlist-section">
            <h3>
              <span className="music-icon">🎵</span>
              Recommended Playlist: <span style={{ color: getMoodColor(result.mood) }}>
                {result.mood.charAt(0).toUpperCase() + result.mood.slice(1)} Vibes
              </span>
            </h3>

            {/* Spotify Embed Player */}
            <div className="spotify-embed">
              <iframe
                src={result.playlist}
                width="100%"
                height="380"
                frameBorder="0"
                allow="encrypted-media"
                title="Spotify Playlist"
                style={{ borderRadius: '12px', border: 'none' }}
              ></iframe>
            </div>
          </div>
        </div>
      )}

      {loading && (
        <div className="loading-overlay">
          <div className="loading-content">
            <div className="loading-spinner"></div>
            <p>Analyzing your emotions and finding the perfect music...</p>
          </div>
        </div>
      )}

      <div className="waves">
        <div className="wave wave1"></div>
        <div className="wave wave2"></div>
        <div className="wave wave3"></div>
      </div>
    </div>
  );
}

export default Home;
