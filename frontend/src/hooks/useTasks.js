import { useCallback, useEffect, useState } from 'react';
import { apiRequest } from '../api';

const EMPTY_FORM = {
  title: '',
  description: '',
  status: 'todo',
  priority: 'medium',
  due_date: '',
};

export function useTasks(user) {
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
  const [aiLoading, setAiLoading] = useState(false);
  const [aiMessage, setAiMessage] = useState('');

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
    if (!user) return undefined;

    const timeout = window.setTimeout(() => {
      loadTasks();
    }, 0);

    return () => window.clearTimeout(timeout);
  }, [user, loadTasks]);

  async function handleSuggestPriority() {
    if (!form.title.trim()) {
      setAiMessage('Enter a task title first.');
      return;
    }

    setAiLoading(true);
    setAiMessage('');

    try {
      const result = await apiRequest('tasks/suggest-priority.php', {
        method: 'POST',
        body: JSON.stringify({
          title: form.title,
          description: form.description,
        }),
      });

      setForm((current) => ({
        ...current,
        priority: result.suggested_priority,
      }));
      setAiMessage(
        `AI suggests ${result.suggested_priority} priority. You can change it before saving.`,
      );
    } catch (error) {
      setAiMessage(error.message || 'Unable to suggest priority.');
    } finally {
      setAiLoading(false);
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

      if (tasks.length === 1 && page > 1) {
        setPage((current) => current - 1);
      } else {
        await loadTasks();
      }
    } catch (error) {
      setTaskMessage(error.message);
    }
  }

  function handleFilterChange(nextFilter) {
    setFilter(nextFilter);
    setPage(1);
  }

  function clearTasks() {
    setTasks([]);
    setTaskMessage('');
  }

  return {
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
    clearTasks,
    loadTasks,
    startCreate,
    startEdit,
    handleTaskSubmit,
    handleDelete,
    handleFilterChange,
    handleSuggestPriority,
  };
}