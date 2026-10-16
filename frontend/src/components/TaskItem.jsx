export default function TaskItem({ task, isAdmin, onEdit, onDelete }) {
  return (
    <article className="task-card">
      <div className="task-card-heading">
        <h3>{task.title}</h3>
        <span className={`status status-${task.status}`}>{task.status}</span>
      </div>

      {task.description && <p>{task.description}</p>}

      <div className="task-meta">
        <span className={`priority priority-${task.priority}`}>
          {task.priority} priority
        </span>
        <span>Due: {task.due_date || 'Not set'}</span>
        {isAdmin && <span>Owner ID: {task.user_id}</span>}
      </div>

      <div className="task-actions">
        <button className="secondary-button" onClick={() => onEdit(task)}>
          Edit
        </button>
        <button className="danger-button" onClick={() => onDelete(task.id)}>
          Delete
        </button>
      </div>
    </article>
  );
}