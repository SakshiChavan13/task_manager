import Pagination from './Pagination';
import TaskItem from './TaskItem';

export default function TaskList({
  user,
  tasks,
  filter,
  page,
  pagination,
  loadingTasks,
  taskMessage,
  onRefresh,
  onFilterChange,
  onEdit,
  onDelete,
  onPageChange,
}) {
  const currentPage = pagination.page || page;
  const totalPages = pagination.total_pages || 1;

  return (
    <section className="panel task-list-panel">
      <div className="list-heading">
        <div>
          <h2>Your tasks</h2>
          <p className="subtitle">{pagination.total} task(s) found</p>
        </div>

        <button
          className="secondary-button"
          onClick={onRefresh}
          disabled={loadingTasks}
        >
          Refresh
        </button>
      </div>

      <label className="filter-label">
        Filter by status
        <select
          value={filter}
          onChange={(event) => onFilterChange(event.target.value)}
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
            <TaskItem
              key={task.id}
              task={task}
              isAdmin={user.role === 'admin'}
              onEdit={onEdit}
              onDelete={onDelete}
            />
          ))}
        </div>
      )}

      <Pagination
        page={currentPage}
        totalPages={totalPages}
        disabled={loadingTasks}
        onPageChange={onPageChange}
      />
    </section>
  );
}