import { useEffect, useState } from 'react';
import { apiRequest } from '../api';

export function useAuth() {
  const [user, setUser] = useState(null);
  const [checkingSession, setCheckingSession] = useState(
    () => Boolean(localStorage.getItem('token')),
  );
  const [mode, setMode] = useState('login');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [authMessage, setAuthMessage] = useState('');
  const [loadingAuth, setLoadingAuth] = useState(false);

  useEffect(() => {
    if (!localStorage.getItem('token')) return undefined;

    let active = true;

    async function checkSession() {
      try {
        const result = await apiRequest('auth/me.php');
        if (active) setUser(result.user);
      } catch {
        localStorage.removeItem('token');
      } finally {
        if (active) setCheckingSession(false);
      }
    }

    checkSession();

    return () => {
      active = false;
    };
  }, []);

  async function handleAuthSubmit(event) {
    event.preventDefault();
    setLoadingAuth(true);
    setAuthMessage('');

    try {
      const endpoint =
        mode === 'login' ? 'auth/login.php' : 'auth/register.php';
      const payload =
        mode === 'login'
          ? { email, password }
          : { name, email, password };
      const result = await apiRequest(endpoint, {
        method: 'POST',
        body: JSON.stringify(payload),
      });

      if (mode === 'register') {
        setMode('login');
        setPassword('');
        setAuthMessage('Account created. Please sign in.');
      } else {
        localStorage.setItem('token', result.token);
        const currentUser = await apiRequest('auth/me.php');
        setUser(currentUser.user);
        setAuthMessage('');
      }
    } catch (error) {
      localStorage.removeItem('token');
      setAuthMessage(error.message);
    } finally {
      setLoadingAuth(false);
    }
  }

  async function signOut() {
    try {
      if (localStorage.getItem('token')) {
        await apiRequest('auth/logout.php', { method: 'POST' });
      }
    } catch {
      // Clear local authentication even if the server cannot be reached.
    } finally {
      localStorage.removeItem('token');
      setUser(null);
      setAuthMessage('');
    }
  }

  return {
    user,
    checkingSession,
    mode,
    setMode,
    name,
    setName,
    email,
    setEmail,
    password,
    setPassword,
    authMessage,
    setAuthMessage,
    loadingAuth,
    handleAuthSubmit,
    signOut,
  };
}