import React, { useState, useEffect, useRef } from 'react';

export default function Chatbox() {
  const navigate = (path) => {
    window.location.href = path;
  };
  const [messages, setMessages] = useState([]);
  const [newMessage, setNewMessage] = useState('');
  const [currentUser, setCurrentUser] = useState(null);
  const [conversation, setConversation] = useState(null);
  const [participants, setParticipants] = useState([]);
  const [loading, setLoading] = useState(true);
  const [conversationId, setConversationId] = useState(null);
  const [sending, setSending] = useState(false);
  const [typing, setTyping] = useState(false);
  const messagesEndRef = useRef(null);
  const messageInputRef = useRef(null);

  useEffect(() => {
    fetchCurrentUser();
    
    // Check for stored conversation data from previous page
    const storedConversationData = localStorage.getItem('currentConversation');
    const storedConversationId = localStorage.getItem('currentConversationId');
    
    if (storedConversationData) {
      try {
        const conversationData = JSON.parse(storedConversationData);
        setConversation(conversationData);
        setParticipants(conversationData.participants || []);
        setConversationId(conversationData.id);
        fetchMessages(conversationData.id);
        setLoading(false);
      } catch (error) {
        console.error('Error parsing stored conversation:', error);
        // Fallback to fetching by ID
        if (storedConversationId) {
          fetchConversationById(storedConversationId);
        } else {
          fetchLatestConversation();
        }
      }
    } else if (storedConversationId) {
      setConversationId(storedConversationId);
      fetchConversationById(storedConversationId);
    } else {
      fetchLatestConversation();
    }
  }, []);

  useEffect(() => {
    if (conversationId) {
      // Poll for new messages every 2 seconds
      const messageInterval = setInterval(() => {
        fetchMessages(conversationId);
      }, 2000);
      
      // Update presence every 30 seconds
      const presenceInterval = setInterval(updatePresence, 30000);

      return () => {
        clearInterval(messageInterval);
        clearInterval(presenceInterval);
      };
    }
  }, [conversationId]);

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  const fetchCurrentUser = async () => {
    try {
      const token = localStorage.getItem('token');
      const response = await fetch('/me', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      });
      const data = await response.json();
      setCurrentUser(data);
    } catch (error) {
      console.error('Error fetching current user:', error);
    }
  };

  const fetchConversationById = async (id) => {
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`/conversations/${id}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      });
      
      if (response.ok) {
        const data = await response.json();
        setConversation(data);
        setParticipants(data.participants || []);
        fetchMessages(id);
      }
      setLoading(false);
    } catch (error) {
      console.error('Error fetching conversation:', error);
      setLoading(false);
    }
  };

//   const fetchLatestConversation = async () => {
//     try {
//       const token = localStorage.getItem('token');
//       const response = await fetch('/conversations/latest', {
//         headers: {
//           'Authorization': `Bearer ${token}`,
//           'Content-Type': 'application/json',
//         },
//       });
      
//       if (response.ok) {
//         const data = await response.json();
//         setConversation(data);
//         setParticipants(data.participants || []);
//         setConversationId(data.id);
//         localStorage.setItem('currentConversationId', data.id);
//         fetchMessages(data.id);
//       }
//       setLoading(false);
//     } catch (error) {
//       console.error('Error fetching latest conversation:', error);
//       setLoading(false);
//     }
//   };

//   const fetchConversation = async () => {
//     try {
//       const token = localStorage.getItem('token');
//       const response = await fetch('/conversations/latest', {
//         headers: {
//           'Authorization': `Bearer ${token}`,
//           'Content-Type': 'application/json',
//         },
//       });
      
//       if (response.ok) {
//         const data = await response.json();
//         setConversation(data);
//         setParticipants(data.participants || []);
//         fetchMessages(data.id);
//       }
//       setLoading(false);
//     } catch (error) {
//       console.error('Error fetching conversation:', error);
//       setLoading(false);
//     }
//   };

  const fetchMessages = async (conversationId = null) => {
    try {
      const token = localStorage.getItem('token');
      const id = conversationId || conversation?.id;
      
      if (!id) return;

      const response = await fetch(`/conversations/${id}/messages`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      });
      
      if (response.ok) {
        const data = await response.json();
        setMessages(data);
      }
    } catch (error) {
      console.error('Error fetching messages:', error);
    }
  };

  const sendMessage = async (e) => {
    if (e && e.preventDefault) {
      e.preventDefault();
    }
    
    if (!newMessage.trim() || sending || !conversationId) return;
    
    setSending(true);
    
    try {
      const token = localStorage.getItem('token');
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      
      const response = await fetch(`/conversations/${conversationId}/messages`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
          content: newMessage.trim(),
        }),
      });

      if (response.ok) {
        setNewMessage('');
        fetchMessages(conversationId); // Refresh messages
      } else {
        console.error('Failed to send message');
      }
    } catch (error) {
      console.error('Error sending message:', error);
    } finally {
      setSending(false);
    }
  };

  const updatePresence = async () => {
    try {
      const token = localStorage.getItem('token');
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      
      await fetch('/update-presence', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
      });
    } catch (error) {
      console.error('Error updating presence:', error);
    }
  };

  const handleInputChange = (e) => {
    setNewMessage(e.target.value);
    
    // Show typing indicator
    if (!typing) {
      setTyping(true);
      setTimeout(() => setTyping(false), 3000);
    }
  };

  const handleKeyPress = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage(e);
    }
  };

  const formatMessageTime = (timestamp) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMinutes = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMinutes / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffMinutes < 1) return 'Just now';
    if (diffMinutes < 60) return `${diffMinutes}m ago`;
    if (diffHours < 24) return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    if (diffDays < 7) return date.toLocaleDateString([], { weekday: 'short', hour: '2-digit', minute: '2-digit' });
    return date.toLocaleDateString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
  };

  const getOtherParticipant = () => {
    return participants.find(p => p.id !== currentUser?.id);
  };

  const otherParticipant = getOtherParticipant();

  if (loading) {
    return (
      <div className="chatbox-loading">
        <div className="loading-spinner"></div>
        <p>Loading conversation...</p>
      </div>
    );
  }

  if (!conversation) {
    return (
      <div className="no-conversation">
        <h3>No conversation found</h3>
        <p>Start a new conversation from the dashboard</p>
        <button onClick={() => navigate('/admin-dashboard')} className="back-button">
          Back to Dashboard
        </button>
      </div>
    );
  }

  return (
    <div className="chatbox-container">
      {/* Header */}
      <div className="chatbox-header">
        <button onClick={() => navigate('/admin-dashboard')} className="back-btn">
          ← Back
        </button>
        <div className="chat-info">
          <div className="participant-avatar">
            {otherParticipant?.name?.charAt(0).toUpperCase() || 'U'}
          </div>
          <div className="participant-details">
            <h3>{otherParticipant?.name || 'Unknown User'}</h3>
            <span className={`status ${otherParticipant?.online_status || 'offline'}`}>
              {otherParticipant?.online_status === 'online' ? 'Online' : 'Offline'}
            </span>
          </div>
        </div>
        <div className="chat-actions">
          <button className="action-btn">⋮</button>
        </div>
      </div>

      {/* Messages */}
      <div className="messages-container">
        <div className="messages-list">
          {messages.length === 0 ? (
            <div className="empty-messages">
              <p>No messages yet. Start the conversation!</p>
            </div>
          ) : (
            messages.map((message) => (
              <div
                key={message.id}
                className={`message ${message.user_id === currentUser?.id ? 'sent' : 'received'}`}
              >
                <div className="message-bubble">
                  <div className="message-content">
                    {message.content}
                  </div>
                  <div className="message-time">
                    {formatMessageTime(message.created_at)}
                  </div>
                </div>
                {message.user_id !== currentUser?.id && (
                  <div className="message-avatar">
                    {message.user?.name?.charAt(0).toUpperCase() || 'U'}
                  </div>
                )}
              </div>
            ))
          )}
          {typing && (
            <div className="typing-indicator">
              <div className="typing-dots">
                <span></span>
                <span></span>
                <span></span>
              </div>
              <span className="typing-text">{otherParticipant?.name} is typing...</span>
            </div>
          )}
          <div ref={messagesEndRef} />
        </div>
      </div>

      {/* Message Input */}
      <div className="message-input-container">
        <div className="message-form">
          <div className="input-wrapper">
            <textarea
              ref={messageInputRef}
              value={newMessage}
              onChange={handleInputChange}
              onKeyPress={handleKeyPress}
              placeholder="Type a message..."
              className="message-input"
              rows="1"
              disabled={sending}
            />
            <button
              type="button"
              onClick={sendMessage}
              disabled={!newMessage.trim() || sending}
              className="send-button"
            >
              {sending ? '⏳' : '→'}
            </button>
          </div>
        </div>
      </div>

      <style jsx>{`
        .chatbox-container {
          display: flex;
          flex-direction: column;
          height: 100vh;
          background: #f5f5f5;
          font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .chatbox-loading, .no-conversation {
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          height: 100vh;
          text-align: center;
          color: #666;
        }

        .loading-spinner {
          width: 40px;
          height: 40px;
          border: 4px solid #f3f3f3;
          border-top: 4px solid #007bff;
          border-radius: 50%;
          animation: spin 1s linear infinite;
          margin-bottom: 20px;
        }

        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }

        .back-button {
          background: #007bff;
          color: white;
          border: none;
          padding: 10px 20px;
          border-radius: 5px;
          cursor: pointer;
          margin-top: 20px;
        }

        .chatbox-header {
          display: flex;
          align-items: center;
          padding: 15px 20px;
          background: white;
          border-bottom: 1px solid #e0e0e0;
          box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .back-btn {
          background: none;
          border: none;
          font-size: 16px;
          cursor: pointer;
          color: #007bff;
          margin-right: 15px;
          padding: 5px 10px;
          border-radius: 5px;
          transition: background 0.2s;
        }

        .back-btn:hover {
          background: #f0f0f0;
        }

        .chat-info {
          display: flex;
          align-items: center;
          flex: 1;
        }

        .participant-avatar {
          width: 45px;
          height: 45px;
          border-radius: 50%;
          background: linear-gradient(135deg, #007bff, #0056b3);
          color: white;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 18px;
          font-weight: bold;
          margin-right: 12px;
        }

        .participant-details h3 {
          margin: 0;
          font-size: 16px;
          color: #333;
        }

        .status {
          font-size: 12px;
          color: #666;
        }

        .status.online {
          color: #28a745;
        }

        .chat-actions {
          display: flex;
          align-items: center;
        }

        .action-btn {
          background: none;
          border: none;
          font-size: 18px;
          cursor: pointer;
          color: #666;
          padding: 8px;
          border-radius: 50%;
          transition: background 0.2s;
        }

        .action-btn:hover {
          background: #f0f0f0;
        }

        .messages-container {
          flex: 1;
          overflow-y: auto;
          padding: 20px;
        }

        .messages-list {
          display: flex;
          flex-direction: column;
          gap: 15px;
        }

        .empty-messages {
          text-align: center;
          color: #666;
          padding: 40px;
        }

        .message {
          display: flex;
          align-items: flex-end;
          gap: 10px;
          max-width: 70%;
        }

        .message.sent {
          align-self: flex-end;
          flex-direction: row-reverse;
        }

        .message.received {
          align-self: flex-start;
        }

        .message-bubble {
          padding: 12px 16px;
          border-radius: 18px;
          max-width: 100%;
          word-wrap: break-word;
        }

        .message.sent .message-bubble {
          background: #007bff;
          color: white;
          border-bottom-right-radius: 6px;
        }

        .message.received .message-bubble {
          background: white;
          color: #333;
          border-bottom-left-radius: 6px;
          box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .message-content {
          line-height: 1.4;
          margin-bottom: 4px;
        }

        .message-time {
          font-size: 11px;
          opacity: 0.7;
        }

        .message-avatar {
          width: 30px;
          height: 30px;
          border-radius: 50%;
          background: linear-gradient(135deg, #6c757d, #495057);
          color: white;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 12px;
          font-weight: bold;
          flex-shrink: 0;
        }

        .typing-indicator {
          display: flex;
          align-items: center;
          gap: 10px;
          color: #666;
          font-size: 14px;
          margin-left: 10px;
        }

        .typing-dots {
          display: flex;
          gap: 3px;
        }

        .typing-dots span {
          width: 6px;
          height: 6px;
          border-radius: 50%;
          background: #666;
          animation: typing 1.4s infinite;
        }

        .typing-dots span:nth-child(1) { animation-delay: 0s; }
        .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .typing-dots span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
          0%, 60%, 100% { opacity: 0.3; }
          30% { opacity: 1; }
        }

        .message-input-container {
          padding: 15px 20px;
          background: white;
          border-top: 1px solid #e0e0e0;
        }

        .message-form {
          width: 100%;
        }

        .input-wrapper {
          display: flex;
          align-items: flex-end;
          gap: 10px;
          background: #f8f9fa;
          border-radius: 25px;
          padding: 8px;
          border: 1px solid #e0e0e0;
        }

        .message-input {
          flex: 1;
          border: none;
          background: none;
          outline: none;
          resize: none;
          padding: 8px 12px;
          font-size: 14px;
          line-height: 1.4;
          max-height: 120px;
          font-family: inherit;
        }

        .send-button {
          width: 36px;
          height: 36px;
          border-radius: 50%;
          border: none;
          background: #007bff;
          color: white;
          font-size: 16px;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          transition: background 0.2s;
        }

        .send-button:hover:not(:disabled) {
          background: #0056b3;
        }

        .send-button:disabled {
          background: #ccc;
          cursor: not-allowed;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
          .chatbox-header {
            padding: 12px 15px;
          }

          .messages-container {
            padding: 15px;
          }

          .message {
            max-width: 85%;
          }

          .participant-avatar {
            width: 40px;
            height: 40px;
            font-size: 16px;
          }

          .message-input-container {
            padding: 12px 15px;
          }
        }
      `}</style>
    </div>
  );
}