export default function TaskForm({
  form,
  setForm,
  editingId,
  savingTask,
  aiLoading,
  aiMessage,
  onSubmit,
  onCancel,
  onSuggestPriority,
}) {
  function updateField(field, value) {
    setForm((current) => ({ ...current, [field]: value }));
  }

  return (
    <section className="panel task-form-panel">
      <h2>{editingId !== null ? 'Edit task' : 'Create a task'}</h2>

      <form onSubmit={onSubmit}>
        <label>
          Task title
          <input
            value={form.title}
            onChange={(event) => updateField('title', event.target.value)}
            required
            maxLength={255}
            placeholder="e.g. Complete API assignment"
          />
        </label>

        <label>
          Description
          <textarea
            value={form.description}
            onChange={(event) =>
              updateField('description', event.target.value)
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
              onChange={(event) => updateField('status', event.target.value)}
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
              onChange={(event) => updateField('priority', event.target.value)}
            >
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>
          </label>
        </div>

        <button
          type="button"
          className="secondary-button"
          onClick={onSuggestPriority}
          disabled={aiLoading}
        >
          {aiLoading ? 'Suggesting...' : '✨ Suggest Priority with AI'}
        </button>

        {aiMessage && <p className="message" role="status">{aiMessage}</p>}

        <label>
          Due date
          <input
            type="date"
            value={form.due_date}
            onChange={(event) => updateField('due_date', event.target.value)}
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
            onClick={onCancel}
          >
            Cancel editing
          </button>
        )}
      </form>
    </section>
  );
}