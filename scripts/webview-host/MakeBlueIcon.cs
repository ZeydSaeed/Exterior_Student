using System;
using System.Drawing;
using System.Drawing.Drawing2D;
using System.Drawing.Imaging;
using System.IO;
using System.Collections.Generic;

internal static class MakeBlueIcon
{
    private static Bitmap Create(int size)
    {
        var bmp = new Bitmap(size, size, PixelFormat.Format32bppArgb);
        using (var g = Graphics.FromImage(bmp))
        {
            g.SmoothingMode = SmoothingMode.AntiAlias;
            g.InterpolationMode = InterpolationMode.HighQualityBicubic;
            g.PixelOffsetMode = PixelOffsetMode.HighQuality;
            g.Clear(Color.Transparent);

            int margin = Math.Max(1, (int)(size * 0.04));
            var rect = new Rectangle(margin, margin, size - 2 * margin, size - 2 * margin);

            using (var path = new GraphicsPath())
            {
                path.AddEllipse(rect);
                using (var brush = new LinearGradientBrush(
                    rect,
                    Color.FromArgb(255, 25, 118, 210),
                    Color.FromArgb(255, 13, 71, 161),
                    45f))
                {
                    g.FillPath(brush, path);
                }
            }

            using (var hi = new SolidBrush(Color.FromArgb(55, 255, 255, 255)))
            {
                g.FillEllipse(
                    hi,
                    margin + (int)(size * 0.12),
                    margin + (int)(size * 0.08),
                    (int)(size * 0.55),
                    (int)(size * 0.35));
            }

            float penW = Math.Max(1.5f, size * 0.045f);
            using (var pen = new Pen(Color.White, penW))
            using (var fill = new SolidBrush(Color.FromArgb(245, 255, 255, 255)))
            {
                pen.StartCap = LineCap.Round;
                pen.EndCap = LineCap.Round;
                pen.LineJoin = LineJoin.Round;

                float cx = size / 2f;
                float cy = size / 2f + size * 0.02f;
                float w = size * 0.58f;
                float h = size * 0.38f;

                using (var left = new GraphicsPath())
                using (var right = new GraphicsPath())
                {
                    left.AddPolygon(new[]
                    {
                        new PointF(cx, cy - h * 0.35f),
                        new PointF(cx - w * 0.48f, cy - h * 0.15f),
                        new PointF(cx - w * 0.48f, cy + h * 0.42f),
                        new PointF(cx, cy + h * 0.22f),
                    });
                    right.AddPolygon(new[]
                    {
                        new PointF(cx, cy - h * 0.35f),
                        new PointF(cx + w * 0.48f, cy - h * 0.15f),
                        new PointF(cx + w * 0.48f, cy + h * 0.42f),
                        new PointF(cx, cy + h * 0.22f),
                    });
                    g.FillPath(fill, left);
                    g.FillPath(fill, right);
                    g.DrawPath(pen, left);
                    g.DrawPath(pen, right);
                }

                g.DrawLine(pen, cx, cy - h * 0.35f, cx, cy + h * 0.22f);

                float capY = cy - h * 0.72f;
                float capSize = size * 0.22f;
                using (var cap = new GraphicsPath())
                {
                    cap.AddPolygon(new[]
                    {
                        new PointF(cx, capY - capSize * 0.35f),
                        new PointF(cx + capSize * 0.7f, capY),
                        new PointF(cx, capY + capSize * 0.2f),
                        new PointF(cx - capSize * 0.7f, capY),
                    });
                    g.FillPath(fill, cap);
                    g.DrawPath(pen, cap);
                }

                g.DrawLine(
                    pen,
                    cx + capSize * 0.55f,
                    capY + capSize * 0.05f,
                    cx + capSize * 0.85f,
                    capY + capSize * 0.55f);
                g.FillEllipse(
                    fill,
                    cx + capSize * 0.78f,
                    capY + capSize * 0.5f,
                    size * 0.06f,
                    size * 0.06f);
            }
        }

        return bmp;
    }

    private static void SaveIco(string path, int[] sizes)
    {
        var images = new List<byte[]>();
        foreach (int s in sizes)
        {
            using (var bmp = Create(s))
            using (var ms = new MemoryStream())
            {
                bmp.Save(ms, ImageFormat.Png);
                images.Add(ms.ToArray());
            }
        }

        using (var fs = File.Create(path))
        using (var bw = new BinaryWriter(fs))
        {
            bw.Write((short)0);
            bw.Write((short)1);
            bw.Write((short)images.Count);
            int offset = 6 + (16 * images.Count);
            for (int i = 0; i < images.Count; i++)
            {
                int s = sizes[i];
                byte dim = (byte)(s >= 256 ? 0 : s);
                bw.Write(dim);
                bw.Write(dim);
                bw.Write((byte)0);
                bw.Write((byte)0);
                bw.Write((short)1);
                bw.Write((short)32);
                bw.Write(images[i].Length);
                bw.Write(offset);
                offset += images[i].Length;
            }

            foreach (byte[] png in images)
            {
                bw.Write(png);
            }
        }
    }

    private static void Main(string[] args)
    {
        string root = args.Length > 0 ? args[0] : ".";
        string ico = Path.Combine(root, "scripts", "app-host", "ExteriorStudent.ico");
        Directory.CreateDirectory(Path.GetDirectoryName(ico));
        int[] sizes = { 16, 24, 32, 48, 64, 128, 256 };
        SaveIco(ico, sizes);

        File.Copy(ico, Path.Combine(root, "scripts", "students-app.ico"), true);
        File.Copy(ico, Path.Combine(root, "public", "icon-students.ico"), true);
        File.Copy(ico, Path.Combine(root, "public", "favicon.ico"), true);

        using (var b32 = Create(32))
        {
            b32.Save(Path.Combine(root, "public", "icon-students-32.png"), ImageFormat.Png);
        }

        using (var b192 = Create(192))
        {
            b192.Save(Path.Combine(root, "public", "icon-students-192.png"), ImageFormat.Png);
        }

        using (var b512 = Create(512))
        {
            b512.Save(Path.Combine(root, "public", "icon-students-512.png"), ImageFormat.Png);
        }

        using (var b180 = Create(180))
        {
            b180.Save(Path.Combine(root, "public", "apple-touch-icon.png"), ImageFormat.Png);
        }

        Console.WriteLine("OK " + ico + " " + new FileInfo(ico).Length);
    }
}
