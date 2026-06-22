import { useLocation, useNavigate } from "react-router-dom";
import { useState } from "react";
import "../styles/auth.css";

function VerifyOtp() {
  const location = useLocation();
  const navigate = useNavigate();

  const email = location.state?.email || "";

  const [otp, setOtp] = useState("");

  const handleVerify = async (e) => {
    e.preventDefault();

    try {
      const response = await fetch(
        "http://127.0.0.1:8000/api/verify-otp",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            email,
            otp,
          }),
        }
      );

      const data = await response.json();

      if (response.ok) {
        alert("Xác thực thành công");
        navigate("/login");
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
        <h2>Xác nhận OTP</h2>

        <p>
          Mã xác nhận đã được gửi tới:
          <br />
          <b>{email}</b>
        </p>

        <form onSubmit={handleVerify}>
          <div className="form-group">
            <label>Mã OTP</label>
            <input
              type="text"
              maxLength="6"
              value={otp}
              onChange={(e) => setOtp(e.target.value)}
              placeholder="Nhập mã OTP"
            />
          </div>

          <button type="submit" className="btn-auth">
            Xác nhận
          </button>
        </form>
      </div>
    </div>
  );
}

export default VerifyOtp;