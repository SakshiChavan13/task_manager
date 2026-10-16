import './App.css';
import AuthForm from './components/AuthForm';
import Dashboard from './components/Dashboard';
import { useAuth } from './hooks/useAuth';
import { useTasks } from './hooks/useTasks';

export default function App() {
  const auth = useAuth();
  const tasks = useTasks(auth.user);

  if (auth.checkingSession) {
    return (
      <main className="page">
        <p>Checking session...</p>
      </main>
    );
  }

  if (!auth.user) {
    return (
      <AuthForm
        mode={auth.mode}
        setMode={auth.setMode}
        name={auth.name}
        setName={auth.setName}
        email={auth.email}
        setEmail={auth.setEmail}
        password={auth.password}
        setPassword={auth.setPassword}
        authMessage={auth.authMessage}
        loadingAuth={auth.loadingAuth}
        onSubmit={auth.handleAuthSubmit}
        onSwitchMode={() => auth.setAuthMessage('')}
      />
    );
  }

  async function signOut() {
    await auth.signOut();
    tasks.clearTasks();
  }

  return (
    <Dashboard
      user={auth.user}
      onSignOut={signOut}
      {...tasks}
    />
  );
}
