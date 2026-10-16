
import { useCallback, useEffect, useState } from 'react';
import { apiRequest } from './api';
import './App.css';

const EMPTY_FORM = {
  title: '',
  description: '',
  status: 'todo',
  priority: 'medium',
  due_date: '',
};

export default function App() {
  const [user, setUser] = useState(null);
  const [checkingSession, setCheckingSession] = useState(true);
  const [mode, setMode] = useState('login');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [authMessage, setAuthMessage] = useState('');
  const [loadingAuth, setLoadingAuth] = useState(false);

  const [tasks, setTasks] = useState([]);
  const [filter, setFilter] = useState('');
  const [page, setPage] = useState(1);
  const [pagination, setPagination] = useState({
    page: 1,
    total: 0,
    total_pages: 0,
  });
  const [loadingTasks, setLoadingTasks] = useState(false);
  const [taskMessage, setTaskMessage] = useState('');

  const [form, setForm] = useState(EMPTY_FORM);
  const [editingId, setEditingId] = useState(null);
  const [savingTask, setSavingTask] = useState(false);

  const signOut = useCallback(async () => {
    try {
      if (localStorage.getItem('token')) {
        await apiRequest('auth/logout.php', { method: 'POST' });
      }
    } catch {
      // Clear the local session even if the server cannot be reached.
    } finally {
      localStorage.removeItem('token');
      setUser(null);
      setTasks([]);
      setTaskMessage('');
      setAuthMessage('');
    }
  }, []);

  useEffect(() => {
    async function checkSession() {
      if (!localStorage.getItem('token')) {
        setCheckingSession(false);
        return;
      }

      try {
        const result = await apiRequest('auth/me.php');
        setUser(result.user);
      } catch {
        localStorage.removeItem('token');
      } finally {
        setCheckingSession(false);
      }
    }

    checkSession();
  }, []);

  const loadTasks = useCallback(async () => {
    setLoadingTasks(true);
    setTaskMessage('');

    try {
      const query = new URLSearchParams({
        page: String(page),
        limit: '10',
      });

      if (filter) query.set('status', filter);

      const result = await apiRequest(`tasks/index.php?${query.toString()}`);
      setTasks(result.data);
      setPagination(result.pagination);
    } catch (error) {
      setTaskMessage(error.message);
    } finally {
      setLoadingTasks(false);
    }
  }, [page, filter]);

  useEffect(() => {
    if (user) loadTasks();
  }, [user, loadTasks]);

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
        const me = await apiRequest('auth/me.php');
        setUser(me.user);
        setAuthMessage('');
      }
    } catch (error) {
      localStorage.removeItem('token');
      setAuthMessage(error.message);
    } finally {
      setLoadingAuth(false);
    }
  }

  function startCreate() {
    setEditingId(null);
    setForm(EMPTY_FORM);
    setTaskMessage('');
  }

  function startEdit(task) {
    setEditingId(task.id);
    setForm({
      title: task.title ?? '',
      description: task.description ?? '',
      status: task.status ?? 'todo',
      priority: task.priority ?? 'medium',
      due_date: task.due_date ?? '',
    });
    setTaskMessage('');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  async function handleTaskSubmit(event) {
    event.preventDefault();
    setSavingTask(true);
    setTaskMessage('');

    try {
      const payload = {
        ...form,
        title: form.title.trim(),
        description: form.description.trim() || null,
        due_date: form.due_date || null,
      };

      if (editingId !== null) {
        await apiRequest(`tasks/update.php?id=${editingId}`, {
          method: 'PUT',
          body: JSON.stringify(payload),
        });
        setTaskMessage('Task updated successfully.');
      } else {
        await apiRequest('tasks/create.php', {
          method: 'POST',
          body: JSON.stringify(payload),
        });
        setTaskMessage('Task created successfully.');
      }

      setForm(EMPTY_FORM);
      setEditingId(null);
      await loadTasks();
    } catch (error) {
      setTaskMessage(error.message);
    } finally {
      setSavingTask(false);
    }
  }

  async function handleDelete(taskId) {
    if (!window.confirm('Are you sure you want to delete this task?')) {
      return;
    }

    setTaskMessage('');

    try {
      await apiRequest(`tasks/delete.php?id=${taskId}`, {
        method: 'DELETE',
      });

      setTaskMessage('Task deleted successfully.');

      // Return to the previous page if this page became empty.
      if (tasks.length === 1 && page > 1) {
        setPage((current) => current - 1);
      } else {
        await loadTasks();
      }
    } catch (error) {
      setTaskMessage(error.message);
    }
  }

  if (checkingSession) {
    return <main className="page"><p>Checking session...</p></main>;
  }

  if (!user) {
    return (
      <main className="page">
        <section className="auth-card">
          <p className="eyebrow">TASK MANAGER</p>
          <h1>{mode === 'login' ? 'Welcome back' : 'Create account'}</h1>
          <p className="subtitle">
            {mode === 'login'
              ? 'Sign in to manage your tasks.'
              : 'Register to get started.'}
          </p>

          <form onSubmit={handleAuthSubmit}>
            {mode === 'register' && (
              <label>
                Full name
                <input
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  required
                  maxLength={100}
                  autoComplete="name"
                />
              </label>
            )}

            <label>
              Email
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                autoComplete="email"
              />
            </label>

            <label>
              Password
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                minLength={8}
                autoComplete={
                  mode === 'login' ? 'current-password' : 'new-password'
                }
              />
            </label>

            <button type="submit" disabled={loadingAuth}>
              {loadingAuth
                ? 'Please wait...'
                : mode === 'login'
                  ? 'Sign in'
                  : 'Create account'}
            </button>
          </form>

          {authMessage && (
            <p className="message" role="status">{authMessage}</p>
          )}

          <p className="switch-mode">
            {mode === 'login'
              ? "Don't have an account?"
              : 'Already registered?'}
            {' '}
            <button
              type="button"
              className="text-button"
              onClick={() => {
                setMode(mode === 'login' ? 'register' : 'login');
                setAuthMessage('');
              }}
            >
              {mode === 'login' ? 'Register' : 'Sign in'}
            </button>
          </p>
        </section>
      </main>
    );
  }

  return (
    <main className="dashboard-page">
      <header className="topbar">
        <div>
          <p className="eyebrow">TASK MANAGER</p>
          <h1>Your workspace</h1>
          <p className="subtitle">
            Signed in as {user.name || user.email}
            {user.role === 'admin' ? ' · Admin' : ''}
          </p>
        </div>
        <button className="secondary-button" onClick={signOut}>
          Log out
        </button>
      </header>

      <section className="dashboard-grid">
        <section className="panel task-form-panel">
          <h2>{editingId !== null ? 'Edit task' : 'Create a task'}</h2>

          <form onSubmit={handleTaskSubmit}>
            <label>
              Task title
              <input
                value={form.title}
                onChange={(e) =>
                  setForm({ ...form, title: e.target.value })
                }
                required
                maxLength={255}
                placeholder="e.g. Complete API assignment"
              />
            </label>

            <label>
              Description
              <textarea
                value={form.description}
                onChange={(e) =>
                  setForm({ ...form, description: e.target.value })
                }
                rows={3}
                placeholder="Add task details..."
              />
            </label>

            <div className="form-row">
              <label>
                Status
                <select
                  value={form.status}
                  onChange={(e) =>
                    setForm({ ...form, status: e.target.value })
                  }
                >
                  <option value="todo">To do</option>
                  <option value="in-progress">In progress</option>
                  <option value="done">Done</option>
                </select>
              </label>

              <label>
                Priority
                <select
                  value={form.priority}
                  onChange={(e) =>
                    setForm({ ...form, priority: e.target.value })
                  }
                >
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
              </label>
            </div>

            <label>
              Due date
              <input
                type="date"
                value={form.due_date}
                onChange={(e) =>
                  setForm({ ...form, due_date: e.target.value })
                }
              />
            </label>

            <button type="submit" disabled={savingTask}>
              {savingTask
                ? 'Saving...'
                : editingId !== null
                  ? 'Save changes'
                  : 'Add task'}
            </button>

            {editingId !== null && (
              <button
                type="button"
                className="secondary-button"
                onClick={startCreate}
              >
                Cancel editing
              </button>
            )}
          </form>
        </section>

        <section className="panel task-list-panel">
          <div className="list-heading">
            <div>
              <h2>Your tasks</h2>
              <p className="subtitle">{pagination.total} task(s) found</p>
            </div>

            <button className="secondary-button" onClick={loadTasks}>
              Refresh
            </button>
          </div>

          <label className="filter-label">
            Filter by status
            <select
              value={filter}
              onChange={(e) => {
                setFilter(e.target.value);
                setPage(1);
              }}
            >
              <option value="">All statuses</option>
              <option value="todo">To do</option>
              <option value="in-progress">In progress</option>
              <option value="done">Done</option>
            </select>
          </label>

          {taskMessage && (
            <p className="message" role="status">{taskMessage}</p>
          )}

          {loadingTasks ? (
            <p>Loading tasks...</p>
          ) : tasks.length === 0 ? (
            <div className="empty-state">
              <h3>No tasks found</h3>
              <p>Create a task or choose another status filter.</p>
            </div>
          ) : (
            <div className="task-list">
              {tasks.map((task) => (
                <article className="task-card" key={task.id}>
                  <div className="task-card-heading">
                    <h3>{task.title}</h3>
                    <span className={`status status-${task.status}`}>
                      {task.status}
                    </span>
                  </div>

                  {task.description && <p>{task.description}</p>}

                  <div className="task-meta">
                    <span className={`priority priority-${task.priority}`}>
                      {task.priority} priority
                    </span>
                    <span>
                      Due: {task.due_date || 'Not set'}
                    </span>
                    {user.role === 'admin' && (
                      <span>Owner ID: {task.user_id}</span>
                    )}
                  </div>

                  <div className="task-actions">
                    <button
                      className="secondary-button"
                      onClick={() => startEdit(task)}
                    >
                      Edit
                    </button>
                    <button
                      className="danger-button"
                      onClick={() => handleDelete(task.id)}
                    >
                      Delete
                    </button>
                  </div>
                </article>
              ))}
            </div>
          )}

          <div className="pagination">
            <button
              className="secondary-button"
              disabled={page <= 1 || loadingTasks}
              onClick={() => setPage((current) => current - 1)}
            >
              Previous
            </button>
            <span>
              Page {pagination.page || page} of {pagination.total_pages || 1}
            </span>
            <button
              className="secondary-button"
              disabled={
                loadingTasks ||
                page >= (pagination.total_pages || 1)
              }
              onClick={() => setPage((current) => current + 1)}
            >
              Next
            </button>
          </div>
        </section>
      </section>
    </main>
  );
}
