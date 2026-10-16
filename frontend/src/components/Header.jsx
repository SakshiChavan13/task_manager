export default function Header({ user, onSignOut }) {
  return (
    <header className="topbar">
      <div>
        <p className="eyebrow">TASK MANAGER</p>
        <h1>Your workspace</h1>
        <p className="subtitle">
          Signed in as {user.name || user.email}
          {user.role === 'admin' ? ' · Admin' : ''}
        </p>
      </div>
      <button className="secondary-button" onClick={onSignOut}>
        Log out
      </button>
    </header>
  );
}