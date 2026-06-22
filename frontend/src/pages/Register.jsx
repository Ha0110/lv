import { Link, useNavigate } from "react-router-dom";
import { useState } from "react";
import "../styles/auth.css";

function Register() {
  const navigate = useNavigate();

  const [form, setForm] = useState({
    email: "",
    hoTen: "",
    sdt: "",
    matKhau: "",
    nhapLaiMatKhau: "",
  });

  const handleChange = (e) => {
    setForm({
      ...form,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (form.matKhau !== form.nhapLaiMatKhau) {
      alert("Mật khẩu không khớp");
      return;
    }

    try {
      const response = await fetch(
        "http://127.0.0.1:8000/api/register",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            email: form.email,
            hoTen: form.hoTen,
            sdt: form.sdt,
            matKhau: form.matKhau,
          }),
        }
      );

      const data = await response.json();

      if (response.ok) {
        alert("Đã gửi mã OTP đến email");

        navigate("/verify-otp", {
          state: {
            email: form.email,
            sdt: form.sdt,
          },
        });
      } else {
        alert(data.message || "Đăng ký thất bại");
      }
    } catch (error) {
      console.error(error);
      alert("Lỗi kết nối server");
    }
  };

  return (
    <div className="auth-container">
      <div className="auth-card">
        <h2>Đăng ký tài khoản</h2>

        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label>Email</label>
            <input
              type="email"
              name="email"
              value={form.email}
              onChange={handleChange}
              placeholder="Nhập email"
            />
          </div>

          <div className="form-group">
            <label>Họ và tên</label>
            <input
              type="text"
              name="hoTen"
              value={form.hoTen}
              onChange={handleChange}
              placeholder="Nhập họ và tên"
            />
          </div>

          <div className="form-group">
            <label>Số điện thoại</label>
            <input
              type="text"
              name="sdt"
              value={form.sdt}
              onChange={handleChange}
              placeholder="Nhập số điện thoại"
            />
          </div>

          <div className="form-group">
            <label>Mật khẩu</label>
            <input
              type="password"
              name="matKhau"
              value={form.matKhau}
              onChange={handleChange}
              placeholder="Nhập mật khẩu"
            />
          </div>

          <div className="form-group">
            <label>Nhập lại mật khẩu</label>
            <input
              type="password"
              name="nhapLaiMatKhau"
              value={form.nhapLaiMatKhau}
              onChange={handleChange}
              placeholder="Nhập lại mật khẩu"
            />
          </div>

          <button type="submit" className="btn-auth">
            Đăng ký
          </button>
        </form>

        <p>
          Đã có tài khoản?
          <Link to="/login"> Đăng nhập</Link>
        </p>
      </div>
    </div>
  );
}

export default Register;