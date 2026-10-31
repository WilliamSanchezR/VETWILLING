#!/usr/bin/env python3
"""Servidor SMTP falso para las pruebas de Medios de envío (solo biblioteca estándar).

Uso: python3 tests/fake_smtp.py <puerto> <carpeta_salida>

- Acepta AUTH PLAIN/LOGIN y guarda en <carpeta>/auth.txt el SHA-256 de la contraseña recibida
  (nunca la contraseña), para comprobar que se usó la credencial descifrada.
- RCPT con "rechazar" en la dirección -> 550 (error permanente).
- RCPT con "temporal" en la dirección -> 451 (error temporal).
- Cada mensaje aceptado se guarda en <carpeta>/msg_<n>.eml
"""
import base64
import hashlib
import os
import socketserver
import sys

PUERTO = int(sys.argv[1])
SALIDA = sys.argv[2]
os.makedirs(SALIDA, exist_ok=True)
contador = {"n": 0}


def guardar_auth(password: str) -> None:
    with open(os.path.join(SALIDA, "auth.txt"), "w", encoding="utf-8") as f:
        f.write(hashlib.sha256(password.encode()).hexdigest())


class Manejador(socketserver.StreamRequestHandler):
    def responder(self, linea: str) -> None:
        self.wfile.write((linea + "\r\n").encode())

    def leer(self) -> str:
        return self.rfile.readline().decode(errors="replace").rstrip("\r\n")

    def handle(self) -> None:
        self.responder("220 fake-smtp listo")
        destinatarios = []
        while True:
            linea = self.leer()
            if not linea and self.rfile.closed:
                return
            comando = linea.upper()
            if comando.startswith(("EHLO", "HELO")):
                self.wfile.write(b"250-fake-smtp\r\n250-AUTH PLAIN LOGIN\r\n250 OK\r\n")
            elif comando.startswith("AUTH PLAIN"):
                partes = linea.split(" ")
                datos = partes[2] if len(partes) > 2 else (self.responder("334 ") or self.leer())
                guardar_auth(base64.b64decode(datos).split(b"\x00")[2].decode())
                self.responder("235 autenticado")
            elif comando.startswith("AUTH LOGIN"):
                self.responder("334 VXNlcm5hbWU6")
                self.leer()
                self.responder("334 UGFzc3dvcmQ6")
                guardar_auth(base64.b64decode(self.leer()).decode())
                self.responder("235 autenticado")
            elif comando.startswith("MAIL FROM"):
                destinatarios = []
                self.responder("250 OK")
            elif comando.startswith("RCPT TO"):
                if "rechazar" in linea.lower():
                    self.responder("550 5.1.1 User unknown")
                elif "temporal" in linea.lower():
                    self.responder("451 4.3.0 Intente mas tarde")
                else:
                    destinatarios.append(linea)
                    self.responder("250 OK")
            elif comando == "DATA":
                self.responder("354 enviar datos")
                cuerpo = []
                while True:
                    l = self.leer()
                    if l == ".":
                        break
                    cuerpo.append(l)
                contador["n"] += 1
                with open(os.path.join(SALIDA, f"msg_{contador['n']}.eml"), "w", encoding="utf-8") as f:
                    f.write("\n".join(cuerpo))
                self.responder("250 OK encolado")
            elif comando == "RSET":
                destinatarios = []
                self.responder("250 OK")
            elif comando == "QUIT":
                self.responder("221 adios")
                return
            elif comando == "":
                return
            else:
                self.responder("250 OK")


class Servidor(socketserver.ThreadingTCPServer):
    allow_reuse_address = True
    daemon_threads = True


if __name__ == "__main__":
    with Servidor(("127.0.0.1", PUERTO), Manejador) as srv:
        srv.serve_forever()
