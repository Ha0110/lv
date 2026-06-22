export default function Profile() {
  const user = JSON.parse(localStorage.getItem("user"));

  if (!user) {
    return <h2>Bạn chưa đăng nhập</h2>;
  }

  return (
    <div className="container" style={{ paddingTop: "120px" }}>
      <h2>Thông tin tài khoản</h2>

      <p>
        <strong>Họ tên:</strong> {user.hoTen}
      </p>

      <p>
        <strong>Email:</strong> {user.email}
      </p>

      <p>
        <strong>Vai trò:</strong> {user.role}
      </p>
    </div>
  );
}