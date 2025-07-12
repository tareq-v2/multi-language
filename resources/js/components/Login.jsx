import React, { useState } from 'react';
import axios from 'axios';
import { useNavigate, Link } from 'react-router-dom';

export default function Login() {
  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });

  const [error, setError] = useState(null); // For showing error messages
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await axios.post('/login', formData);
      if (response.data.token) {
        localStorage.setItem('token', response.data.token); // Store token in localStorage
        navigate('/admin-dashboard'); // Redirect to admin dashboard
      }
    } catch (error) {
      setError('Invalid email or password. Please try again.'); // Show error message
    }
  };

  return (
    <div className="login-container">
      <h2>Login</h2>
      {error && <div className="error-message">{error}</div>} {/* Show error message */}
      <form onSubmit={handleSubmit}>
        <div className="input-group">
          <label>Email:</label>
          <input
            type="email"
            value={formData.email}
            onChange={(e) => setFormData({ ...formData, email: e.target.value })}
            required
          />
        </div>
        <div className="input-group">
          <label>Password:</label>
          <input
            type="password"
            value={formData.password}
            onChange={(e) => setFormData({ ...formData, password: e.target.value })}
            required
          />
        </div>
        <button type="submit" className="login-button">Login</button>
      </form>
      <p>
        Don't have an account? <Link to="/register">Register here</Link> 
      </p>
    </div>
  );
}