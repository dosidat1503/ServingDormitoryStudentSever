module.exports = {
    // Cấu hình các quy tắc kiểm tra cho mã JavaScript
    // Ví dụ: https://eslint.org/docs/rules/
    rules: {
      // Cấu hình các quy tắc kiểm tra cho mã JavaScript ở đây
      // Ví dụ:
      'no-console': 'off', // Cho phép sử dụng console
      'no-unused-vars': 'warn', // Cảnh báo về biến chưa sử dụng
    },
    // Cấu hình các quy tắc kiểm tra cho mã HTML và CSS
    overrides: [
      {
        files: ['**/*.html'], // Kiểm tra tất cả các tệp HTML
        extends: ['plugin:@html-eslint/recommended'], // Sử dụng các quy tắc kiểm tra cho HTML
      },
    ],
  };