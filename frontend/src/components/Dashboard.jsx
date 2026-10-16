import Header from './Header';
import TaskForm from './TaskForm';
import TaskList from './TaskList';

export default function Dashboard({
  user,
  onSignOut,
  tasks,
  filter,
  page,
  pagination,
  loadingTasks,
  taskMessage,
  form,
  editingId,
  savingTask,
  aiLoading,
  aiMessage,
  setForm,
  setPage,
  loadTasks,
  startCreate,
  startEdit,
  handleTaskSubmit,
  handleDelete,
  handleFilterChange,
  handleSuggestPriority,
}) {
  return (
    <main className="dashboard-page">
      <Header user={user} onSignOut={onSignOut} />

      <section className="dashboard-grid">
        <TaskForm
          form={form}
          setForm={setForm}
          editingId={editingId}
          savingTask={savingTask}
          aiLoading={aiLoading}
          aiMessage={aiMessage}
          onSubmit={handleTaskSubmit}
          onCancel={startCreate}
          onSuggestPriority={handleSuggestPriority}
        />

        <TaskList
          user={user}
          tasks={tasks}
          filter={filter}
          page={page}
          pagination={pagination}
          loadingTasks={loadingTasks}
          taskMessage={taskMessage}
          onRefresh={loadTasks}
          onFilterChange={handleFilterChange}
          onEdit={startEdit}
          onDelete={handleDelete}
          onPageChange={setPage}
        />
      </section>
    </main>
  );
}
