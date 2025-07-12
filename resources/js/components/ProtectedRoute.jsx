import React from 'react';
import { Navigate } from 'react-router-dom';

export default function ProtectedRoute({ children }) {
  const isAuthenticated = !!localStorage.getItem('token'); // Check if user is authenticated
  return isAuthenticated ? children : <Navigate to="/login" />; // Redirect to login if not authenticated
}