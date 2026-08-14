from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

out = Path(__file__).resolve().parents[1] / "public"
brand = (228, 0, 43, 255)  # #e4002b
white = (255, 255, 255, 255)


def make_circle(size: int) -> Image.Image:
    img = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    draw = ImageDraw.Draw(img)
    pad = max(1, size // 64)
    draw.ellipse([pad, pad, size - 1 - pad, size - 1 - pad], fill=brand)

    font = None
    candidates = [
        r"C:\Windows\Fonts\arialbd.ttf",
        r"C:\Windows\Fonts\segoeuib.ttf",
        r"C:\Windows\Fonts\arial.ttf",
        r"C:\Windows\Fonts\segoeui.ttf",
    ]
    font_size = int(size * 0.58)
    for path in candidates:
        try:
            font = ImageFont.truetype(path, font_size)
            break
        except OSError:
            continue
    if font is None:
        font = ImageFont.load_default()

    text = "S"
    bbox = draw.textbbox((0, 0), text, font=font)
    tw, th = bbox[2] - bbox[0], bbox[3] - bbox[1]
    x = (size - tw) / 2 - bbox[0]
    y = (size - th) / 2 - bbox[1] + size * 0.02
    draw.text((x, y), text, font=font, fill=white)
    return img


png_sizes = {
    "favicon-32x32.png": 32,
    "favicon-48x48.png": 48,
    "apple-touch-icon.png": 180,
    "android-chrome-192x192.png": 192,
    "android-chrome-512x512.png": 512,
}
for name, size in png_sizes.items():
    make_circle(size).save(out / name, format="PNG")
    print("wrote", name)

ico_imgs = [make_circle(s) for s in (16, 32, 48)]
ico_imgs[0].save(
    out / "favicon.ico",
    format="ICO",
    sizes=[(16, 16), (32, 32), (48, 48)],
)
print("wrote favicon.ico")

svg = """<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" role="img" aria-label="SeatWeb">
  <circle cx="32" cy="32" r="32" fill="#e4002b"/>
  <text x="32" y="34" text-anchor="middle" dominant-baseline="middle"
        font-family="Arial Black, Arial, Helvetica, sans-serif"
        font-size="38" font-weight="900" fill="#ffffff">S</text>
</svg>
"""
(out / "favicon.svg").write_text(svg, encoding="utf-8")
print("wrote favicon.svg")
print("done")
