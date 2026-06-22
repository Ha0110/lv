// Danh sách vai trò dùng chung cho bảng người dùng và phần nhận diện admin.
export const roles = [
  { value: "customer", label: "Khách hàng" },
  { value: "admin", label: "Quản trị" },
  { value: "owner", label: "Chủ cửa hàng" },
];

export const roleLabels = roles.reduce((labels, role) => {
  labels[role.value] = role.label;
  return labels;
}, {});

export const getCurrentUser = () => {
  // Login hiện lưu thông tin người dùng trong localStorage.
  try {
    return JSON.parse(localStorage.getItem("user"));
  } catch {
    return null;
  }
};

export const formatDate = (value) => {
  // Chuẩn hóa ngày từ MySQL/Laravel sang định dạng Việt Nam.
  if (!value) return "Chưa có";

  const date = new Date(String(value).replace(" ", "T"));

  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return new Intl.DateTimeFormat("vi-VN", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  }).format(date);
};

export const formatMoney = (value) =>
  new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(Number(value || 0));
