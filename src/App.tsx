import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { useState, useEffect } from 'react';
import { onAuthStateChanged, User } from 'firebase/auth';
import { auth } from './lib/firebase';
import Home from './pages/Home';
import Admin from './pages/Admin';
import ProjectDetail from './pages/ProjectDetail';
import Navbar from './components/Navbar';
import ErrorBoundary from './components/ErrorBoundary';

export default function App() {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const unsubscribe = onAuthStateChanged(auth, (currentUser) => {
      setUser(currentUser);
      setLoading(false);
    });
    return () => unsubscribe();
  }, []);

  if (loading) {
    return (
      <div className="min-h-screen bg-pitch-black flex items-center justify-center">
        <div className="w-12 h-12 border-4 border-sharp-orange border-t-transparent rounded-full animate-spin"></div>
      </div>
    );
  }

  return (
    <Router>
      <div className="min-h-screen bg-pitch-black flex flex-col">
        <Navbar user={user} />
        <main className="flex-1 px-4 md:px-10 py-5">
          <ErrorBoundary>
            <Routes>
              <Route path="/" element={<Home />} />
              <Route path="/project/:slug" element={<ProjectDetail />} />
              <Route 
                path="/admin" 
                element={user?.email === 'philmorehost@gmail.com' ? <Admin /> : <Navigate to="/" />} 
              />
            </Routes>
          </ErrorBoundary>
        </main>
      </div>
    </Router>
  );
}
