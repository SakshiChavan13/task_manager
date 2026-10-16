export default function Pagination({
  page,
  totalPages,
  disabled,
  onPageChange,
}) {
  return (
    <div className="pagination">
      <button
        className="secondary-button"
        disabled={page <= 1 || disabled}
        onClick={() => onPageChange((current) => current - 1)}
      >
        Previous
      </button>
      <span>
        Page {page} of {totalPages}
      </span>
      <button
        className="secondary-button"
        disabled={disabled || page >= totalPages}
        onClick={() => onPageChange((current) => current + 1)}
      >
        Next
      </button>
    </div>
  );
}