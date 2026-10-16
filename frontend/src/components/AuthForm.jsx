
export default function AuthForm({
  mode,
  setMode,
  name,
  setName,
  email,
  setEmail,
  password,
  setPassword,
  authMessage,
  loadingAuth,
  onSubmit,
  onSwitchMode,
}) {
  return (
    <main className="page">
      <section className="auth-card">
        <p className="eyebrow">TASK MANAGER</p>

        <h1>
          {mode === 'login' ? 'Welcome back' : 'Create account'}
        </h1>

        <p className="subtitle">
          {mode === 'login'
            ? 'Sign in to manage your tasks.'
            : 'Register to get started.'}
        </p>

        <form onSubmit={onSubmit}>
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
                mode === 'login'
                  ? 'current-password'
                  : 'new-password'
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
          <p className="message" role="status">
            {authMessage}
          </p>
        )}

        <p className="switch-mode">
          {mode === 'login'
            ? "Don't have an account?"
            : 'Already registered?'}{' '}

          <button
            type="button"
            className="text-button"
            onClick={() => {
              setMode(mode === 'login' ? 'register' : 'login');
              onSwitchMode();
            }}
          >
            {mode === 'login' ? 'Register' : 'Sign in'}
          </button>
        </p>
      </section>
    </main>
  );
}
