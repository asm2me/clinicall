type Column<T> = {
  header: string;
  cell: (row: T) => string;
};

type DataTableProps<T> = {
  columns: Column<T>[];
  rows: T[];
  getRowKey: (row: T) => string;
};

export function DataTable<T>({ columns, rows, getRowKey }: DataTableProps<T>) {
  return (
    <table className="data-table">
      <thead>
        <tr>
          {columns.map((column) => (
            <th key={column.header}>{column.header}</th>
          ))}
        </tr>
      </thead>
      <tbody>
        {rows.map((row) => (
          <tr key={getRowKey(row)}>
            {columns.map((column) => (
              <td key={column.header}>{column.cell(row)}</td>
            ))}
          </tr>
        ))}
      </tbody>
    </table>
  );
}