import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import Layout from './components/Layout';
import Home from './components/Home';
import Login from './components/Login';
import Register from './components/Register';
import AdminDashboard from './components/AdminDashboard';
import ProtectedRoute from './components/ProtectedRoute';
import Chat from './components/Chat.jsx';
import Chatbox from './components/Chatbox.jsx';
import MoodHistory from './components/playlists/MoodHistory.jsx';
import PlaylistResult from './components/playlists/PlaylistResult.jsx';

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Layout><Home /></Layout>} />
        <Route path="/login" element={<Layout><Login /></Layout>} />
        <Route path="/register" element={<Layout><Register /></Layout>} />
        <Route path="/mood/history" element={<Layout><MoodHistory  /></Layout>} />
        <Route path="/playlists/result" element={<Layout><PlaylistResult /></Layout>} />
        <Route
          path="/admin-dashboard"
          element={
            <ProtectedRoute>
              <Layout><AdminDashboard /></Layout>
            </ProtectedRoute>
          }
        />
        <Route
          path="/Chatbox"
          element={
            <ProtectedRoute>
              <Layout><Chat /></Layout>
            </ProtectedRoute>
          }
        />
        {/* <Route
          path="/Chatbox"
          element={
            <ProtectedRoute>
              <Layout><Chatbox /></Layout>
            </ProtectedRoute>
          }
        /> */}
      </Routes>
    </BrowserRouter>
  );
}

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(<App />);

