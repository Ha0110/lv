import { useState } from "react";
import { useNavigate } from "react-router-dom";

function Login() {
  const navigate = useNavigate();

  const [email, setEmail] = useState("");
  const [matKhau, setMatKhau] = useState("");

  const handleLogin = async (e) => {
    e.preventDefault();

    try {
      const response = await fetch(
        "http://127.0.0.1:8000/api/login",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            email,
            matKhau,
          }),
        }
      );

      const data = await response.json();

      if (response.ok) {
        localStorage.setItem(
          "user",
          JSON.stringify(data.user)
        );

        alert("Đăng nhập thành công");
        navigate(["admin", "owner"].includes(data.user.role) ? "/admin" : "/");
      } else {
        alert(data.message);
      }
    } catch (error) {
      console.error(error);
      alert("Lỗi kết nối");
    }
  };

  return (
    <div className="auth-container">
      <div className="auth-card">
        <h2>Đăng nhập</h2>

        <form onSubmit={handleLogin}>
          <div className="form-group">
            <label>Email</label>
            <input
              type="email"
              value={email}
              onChange={(e) =>
                setEmail(e.target.value)
              }
            />
          </div>

          <div className="form-group">
            <label>Mật khẩu</label>
            <input
              type="password"
              value={matKhau}
              onChange={(e) =>
                setMatKhau(e.target.value)
              }
            />
          </div>

          <button
            type="submit"
            className="btn-auth"
          >
            Đăng nhập
          </button>
        </form>
        <p>
          Chưa có tài khoản?
            <a href="/register"> Đăng ký</a>
        </p>
      </div>
    </div>
  );
}

export default Login;
