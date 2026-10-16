
const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL ||
  'http://localhost/task-manager/backend/api';

export async function apiRequest(endpoint, options = {}) {
  const token = localStorage.getItem('token');

  const headers = {
    'Content-Type': 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...options.headers,
  };

  const response = await fetch(`${API_BASE_URL}/${endpoint}`, {
    ...options,
    headers,
  });

  const responseText = await response.text();
  let result;

  try {
    result = responseText ? JSON.parse(responseText) : {};
  } catch {
    if (!response.ok) {
      throw new Error(responseText || `Request failed (${response.status}).`);
    }

    throw new Error('The server returned an invalid response.');
  }

  if (!response.ok) {
    throw new Error(
      result.message || `Request failed (${response.status}).`,
    );
  }

  return result;
}
