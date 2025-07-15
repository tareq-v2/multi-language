import React, { useEffect, useState } from 'react';
import axios from 'axios';

const MoodHistory = () => {
  const [history, setHistory] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchHistory = async () => {
      try {
        const response = await axios.get('/mood/history');
        setHistory(response.data);
      } catch (err) {
        console.error('Failed to fetch history:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchHistory();
  }, []);

  const moodEmojis = {
    happy: '😄',
    sad: '😢',
    calm: '😌',
    neutral: '😐'
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString();
  };

  if (loading) {
    return <div className="loading">Loading history...</div>;
  }

  if (history.length === 0) {
    return <div className="no-history">No mood history yet</div>;
  }

  return (
    <div className="mood-history">
      <h2>Your Mood History</h2>
      <div className="history-items">
        {history.map((item) => (
          <div key={item.id} className={`history-item mood-${item.mood}`}>
            <div className="mood-header">
              <span className="mood-emoji">{moodEmojis[item.mood] || '😐'}</span>
              <span className="mood-name">{item.mood}</span>
              <span className="history-date">{formatDate(item.created_at)}</span>
            </div>
            <div className="history-text">{item.text}</div>
            <div className="sentiment-info">
              <span>Polarity: {item.polarity.toFixed(2)}</span>
              <span>Subjectivity: {item.subjectivity.toFixed(2)}</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default MoodHistory;
