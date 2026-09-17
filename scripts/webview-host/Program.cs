using System;
using System.Drawing;
using System.IO;
using System.Runtime.InteropServices;
using System.Windows.Forms;
using Microsoft.Web.WebView2.WinForms;

namespace ExteriorStudentHost
{
    internal static class Program
    {
        private const string AppUserModelId = "ExteriorStudent.DesktopApp";
        internal const string WindowTitle = "نظام إدارة الطلبة الخريجون - الإصدار 1";

        [DllImport("shell32.dll", CharSet = CharSet.Unicode, SetLastError = true)]
        private static extern int SetCurrentProcessExplicitAppUserModelID(string appID);

        [STAThread]
        private static void Main(string[] args)
        {
            try
            {
                SetCurrentProcessExplicitAppUserModelID(AppUserModelId);
            }
            catch
            {
                // ignore on older systems
            }

            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);

            string url = "http://exterior_student.test";
            if (args != null && args.Length > 0 && !string.IsNullOrWhiteSpace(args[0]))
            {
                url = args[0].Trim();
            }

            string baseDir = AppDomain.CurrentDomain.BaseDirectory;
            string iconPath = Path.Combine(baseDir, "ExteriorStudent.ico");

            HostForm form = new HostForm(iconPath);
            form.StartPosition = FormStartPosition.CenterScreen;
            form.WindowState = FormWindowState.Maximized;
            form.MinimumSize = new Size(900, 600);

            WebView2 web = new WebView2();
            web.Dock = DockStyle.Fill;
            form.ContentHost.Controls.Add(web);

            form.Shown += async (sender, e) =>
            {
                try
                {
                    string userData = Path.Combine(
                        Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData),
                        "ExteriorStudent",
                        "WebView2");
                    Directory.CreateDirectory(userData);

                    string origin = url;
                    try
                    {
                        origin = new Uri(url).GetLeftPart(UriPartial.Authority);
                    }
                    catch
                    {
                        // keep the launch url if it is not a full URI
                    }

                    var options = new Microsoft.Web.WebView2.Core.CoreWebView2EnvironmentOptions(
                        additionalBrowserArguments: "--unsafely-treat-insecure-origin-as-secure=" + origin);
                    var env = await Microsoft.Web.WebView2.Core.CoreWebView2Environment.CreateAsync(
                        browserExecutableFolder: null,
                        userDataFolder: userData,
                        options: options);
                    await web.EnsureCoreWebView2Async(env);
                    web.CoreWebView2.Settings.AreDefaultContextMenusEnabled = true;
                    web.CoreWebView2.Settings.AreDevToolsEnabled = false;
                    web.CoreWebView2.Settings.IsZoomControlEnabled = true;
                    web.CoreWebView2.DocumentTitleChanged += (titleSender, titleArgs) =>
                    {
                        form.Text = WindowTitle;
                    };
                    web.CoreWebView2.NavigationCompleted += (navSender, navArgs) =>
                    {
                        form.Text = WindowTitle;
                    };
                    web.CoreWebView2.DownloadStarting += (downloadSender, downloadArgs) =>
                    {
                        using (SaveFileDialog dialog = new SaveFileDialog())
                        {
                            string suggested = downloadArgs.ResultFilePath;
                            bool isBackup = string.Equals(
                                Path.GetExtension(suggested),
                                ".esbak",
                                StringComparison.OrdinalIgnoreCase)
                                || string.Equals(
                                Path.GetExtension(suggested),
                                ".sql",
                                StringComparison.OrdinalIgnoreCase);

                            dialog.Title = isBackup ? "حفظ النسخة الاحتياطية" : "حفظ الملف";
                            dialog.Filter = isBackup
                                ? "نسخة مشفّرة (*.esbak)|*.esbak|SQL (*.sql)|*.sql|كل الملفات (*.*)|*.*"
                                : "كل الملفات (*.*)|*.*";
                            dialog.DefaultExt = isBackup ? "esbak" : "";
                            dialog.AddExtension = isBackup;
                            dialog.OverwritePrompt = true;
                            dialog.RestoreDirectory = true;
                            dialog.InitialDirectory = Environment.GetFolderPath(
                                Environment.SpecialFolder.DesktopDirectory);
                            dialog.FileName = string.IsNullOrWhiteSpace(suggested)
                                ? (isBackup ? "backup.esbak" : "download")
                                : Path.GetFileName(suggested);

                            if (dialog.ShowDialog(form) == DialogResult.OK)
                            {
                                downloadArgs.ResultFilePath = dialog.FileName;
                            }
                            else
                            {
                                downloadArgs.Cancel = true;
                            }
                        }
                    };
                    form.Text = WindowTitle;
                    web.CoreWebView2.Navigate(url);
                }
                catch (Exception ex)
                {
                    MessageBox.Show(
                        "تعذر فتح البرنامج داخل العارض." + Environment.NewLine +
                        "تأكد من تشغيل Herd و MySQL ثم أعد المحاولة." + Environment.NewLine +
                        Environment.NewLine + ex.Message,
                        "نظام الطلبة",
                        MessageBoxButtons.OK,
                        MessageBoxIcon.Error);
                    form.Close();
                }
            };

            Application.Run(form);
        }
    }

    internal sealed class HostForm : Form
    {
        internal const string WindowTitle = Program.WindowTitle;
        private const int TitleBarHeight = 32;
        private const int CaptionButtonWidth = 46;
        private const int IconSlotWidth = 36;
        private const int ResizeBorder = 8;
        private const int WM_NCHITTEST = 0x0084;
        private const int WM_NCLBUTTONDOWN = 0x00A1;
        private const int HTLEFT = 10;
        private const int HTRIGHT = 11;
        private const int HTTOP = 12;
        private const int HTTOPLEFT = 13;
        private const int HTTOPRIGHT = 14;
        private const int HTBOTTOM = 15;
        private const int HTBOTTOMLEFT = 16;
        private const int HTBOTTOMRIGHT = 17;
        private const int HTCAPTION = 2;

        private readonly Panel _titleBar;
        private readonly Panel _contentHost;
        private readonly Label _title;
        private readonly PictureBox _iconBox;
        private readonly Button _minButton;
        private readonly Button _maxButton;
        private readonly Button _closeButton;
        private readonly Font _symbolFont;

        [DllImport("user32.dll")]
        private static extern bool ReleaseCapture();

        [DllImport("user32.dll")]
        private static extern IntPtr SendMessage(IntPtr hWnd, int msg, int wParam, int lParam);

        public Panel ContentHost
        {
            get { return _contentHost; }
        }

        public HostForm(string iconPath)
        {
            Text = WindowTitle;
            FormBorderStyle = FormBorderStyle.None;
            DoubleBuffered = true;
            ShowIcon = true;
            ShowInTaskbar = true;
            BackColor = Color.White;
            ForeColor = Color.FromArgb(26, 26, 26);

            if (!string.IsNullOrEmpty(iconPath) && File.Exists(iconPath))
            {
                try
                {
                    Icon = new Icon(iconPath);
                }
                catch
                {
                    // ignore icon load errors
                }
            }

            try
            {
                _symbolFont = new Font("Segoe MDL2 Assets", 8.5f, FontStyle.Regular);
            }
            catch
            {
                _symbolFont = new Font("Segoe UI", 10f, FontStyle.Regular);
            }

            _titleBar = new Panel();
            _titleBar.Height = TitleBarHeight;
            _titleBar.Dock = DockStyle.Top;
            _titleBar.BackColor = Color.White;

            _contentHost = new Panel();
            _contentHost.Dock = DockStyle.Fill;
            _contentHost.BackColor = Color.White;

            _title = new Label();
            _title.Text = WindowTitle;
            _title.Dock = DockStyle.Fill;
            _title.TextAlign = ContentAlignment.MiddleCenter;
            _title.ForeColor = Color.FromArgb(26, 26, 26);
            _title.BackColor = Color.Transparent;
            _title.Font = new Font("Segoe UI", 10.5f, FontStyle.Regular);
            _title.RightToLeft = RightToLeft.Yes;
            _title.AutoEllipsis = true;

            _iconBox = new PictureBox();
            _iconBox.Width = IconSlotWidth;
            _iconBox.Height = TitleBarHeight;
            _iconBox.Left = 0;
            _iconBox.Top = 0;
            _iconBox.Anchor = AnchorStyles.Top | AnchorStyles.Left;
            _iconBox.SizeMode = PictureBoxSizeMode.CenterImage;
            _iconBox.BackColor = Color.Transparent;
            if (Icon != null)
            {
                try
                {
                    using (Icon small = new Icon(Icon, 16, 16))
                    {
                        _iconBox.Image = small.ToBitmap();
                    }
                }
                catch
                {
                    // keep an empty icon slot if conversion fails
                }
            }

            _minButton = CreateCaptionButton("\uE921", "تصغير");
            _maxButton = CreateCaptionButton("\uE922", "تكبير");
            _closeButton = CreateCaptionButton("\uE8BB", "إغلاق");
            _closeButton.MouseEnter += delegate
            {
                _closeButton.BackColor = Color.FromArgb(196, 43, 28);
                _closeButton.ForeColor = Color.White;
            };
            _closeButton.MouseLeave += delegate
            {
                _closeButton.BackColor = Color.White;
                _closeButton.ForeColor = Color.FromArgb(26, 26, 26);
            };

            _minButton.Click += delegate { WindowState = FormWindowState.Minimized; };
            _maxButton.Click += delegate { ToggleMaximize(); };
            _closeButton.Click += delegate { Close(); };

            _title.MouseDown += TitleBarMouseDown;
            _title.DoubleClick += delegate { ToggleMaximize(); };
            _titleBar.MouseDown += TitleBarMouseDown;
            _titleBar.DoubleClick += delegate { ToggleMaximize(); };
            _iconBox.MouseDown += TitleBarMouseDown;
            _iconBox.DoubleClick += delegate { ToggleMaximize(); };

            _titleBar.Controls.Add(_title);
            _titleBar.Controls.Add(_iconBox);
            _titleBar.Controls.Add(_minButton);
            _titleBar.Controls.Add(_maxButton);
            _titleBar.Controls.Add(_closeButton);
            _iconBox.BringToFront();
            _minButton.BringToFront();
            _maxButton.BringToFront();
            _closeButton.BringToFront();

            Controls.Add(_contentHost);
            Controls.Add(_titleBar);
            Resize += delegate { LayoutCaptionButtons(); };
            LayoutCaptionButtons();
            SyncMaximizeGlyph();
        }

        protected override CreateParams CreateParams
        {
            get
            {
                CreateParams cp = base.CreateParams;
                cp.Style |= 0x00020000;
                cp.Style |= 0x00010000;
                cp.ClassStyle |= 0x00020000;
                return cp;
            }
        }

        protected override void OnResize(EventArgs e)
        {
            base.OnResize(e);
            Padding = WindowState == FormWindowState.Maximized ? new Padding(0) : new Padding(1);
            if (IsHandleCreated)
            {
                Screen screen = Screen.FromHandle(Handle);
                if (screen != null)
                {
                    MaximizedBounds = screen.WorkingArea;
                }
            }
            LayoutCaptionButtons();
            SyncMaximizeGlyph();
            Invalidate();
        }

        protected override void OnPaint(PaintEventArgs e)
        {
            base.OnPaint(e);
            if (WindowState != FormWindowState.Maximized)
            {
                using (Pen pen = new Pen(Color.FromArgb(74, 84, 94)))
                {
                    e.Graphics.DrawRectangle(pen, 0, 0, Width - 1, Height - 1);
                }
            }
        }

        protected override void WndProc(ref Message m)
        {
            if (m.Msg == WM_NCHITTEST && WindowState != FormWindowState.Maximized)
            {
                base.WndProc(ref m);
                int xy = unchecked((int)(long)m.LParam);
                Point cursor = PointToClient(new Point((short)(xy & 0xFFFF), (short)((xy >> 16) & 0xFFFF)));
                bool left = cursor.X <= ResizeBorder;
                bool right = cursor.X >= ClientSize.Width - ResizeBorder;
                bool top = cursor.Y <= ResizeBorder;
                bool bottom = cursor.Y >= ClientSize.Height - ResizeBorder;
                if (top && left)
                {
                    m.Result = (IntPtr)HTTOPLEFT;
                    return;
                }
                if (top && right)
                {
                    m.Result = (IntPtr)HTTOPRIGHT;
                    return;
                }
                if (bottom && left)
                {
                    m.Result = (IntPtr)HTBOTTOMLEFT;
                    return;
                }
                if (bottom && right)
                {
                    m.Result = (IntPtr)HTBOTTOMRIGHT;
                    return;
                }
                if (left)
                {
                    m.Result = (IntPtr)HTLEFT;
                    return;
                }
                if (right)
                {
                    m.Result = (IntPtr)HTRIGHT;
                    return;
                }
                if (top)
                {
                    m.Result = (IntPtr)HTTOP;
                    return;
                }
                if (bottom)
                {
                    m.Result = (IntPtr)HTBOTTOM;
                    return;
                }
                return;
            }

            base.WndProc(ref m);
        }

        private Button CreateCaptionButton(string glyph, string accessibleName)
        {
            Button button = new Button();
            button.FlatStyle = FlatStyle.Flat;
            button.FlatAppearance.BorderSize = 0;
            button.FlatAppearance.MouseOverBackColor = Color.FromArgb(229, 229, 229);
            button.Text = glyph;
            button.Font = _symbolFont;
            button.ForeColor = Color.FromArgb(26, 26, 26);
            button.BackColor = Color.White;
            button.TabStop = false;
            button.Size = new Size(CaptionButtonWidth, TitleBarHeight);
            button.AccessibleName = accessibleName;
            button.Anchor = AnchorStyles.Top | AnchorStyles.Right;
            return button;
        }

        private void LayoutCaptionButtons()
        {
            if (_titleBar == null || _closeButton == null || _maxButton == null || _minButton == null || _iconBox == null)
            {
                return;
            }

            int top = 0;
            int height = _titleBar.Height;
            int width = CaptionButtonWidth;
            _iconBox.SetBounds(0, top, IconSlotWidth, height);
            _closeButton.SetBounds(_titleBar.Width - width, top, width, height);
            _maxButton.SetBounds(_titleBar.Width - (width * 2), top, width, height);
            _minButton.SetBounds(_titleBar.Width - (width * 3), top, width, height);
        }

        private void SyncMaximizeGlyph()
        {
            if (_maxButton == null)
            {
                return;
            }

            bool maximized = WindowState == FormWindowState.Maximized;
            _maxButton.Text = maximized ? "\uE923" : "\uE922";
            _maxButton.AccessibleName = maximized ? "استعادة" : "تكبير";
        }

        private void ToggleMaximize()
        {
            WindowState = WindowState == FormWindowState.Maximized
                ? FormWindowState.Normal
                : FormWindowState.Maximized;
        }

        private void TitleBarMouseDown(object sender, MouseEventArgs e)
        {
            if (e.Button != MouseButtons.Left || e.Clicks != 1)
            {
                return;
            }

            ReleaseCapture();
            SendMessage(Handle, WM_NCLBUTTONDOWN, HTCAPTION, 0);
        }
    }
}
