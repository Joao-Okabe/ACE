@echo off
setlocal
set APP_HOME=%~dp0
set WRAPPER_JAR=%APP_HOME%gradle\wrapper\gradle-wrapper.jar
set WRAPPER_URL=https://github.com/gradle/gradle/raw/refs/tags/v8.12.1/gradle/wrapper/gradle-wrapper.jar

if not exist "%WRAPPER_JAR%" (
  echo Baixando o Gradle Wrapper...
  powershell -NoProfile -ExecutionPolicy Bypass -Command "Invoke-WebRequest -Uri '%WRAPPER_URL%' -OutFile '%WRAPPER_JAR%'"
  if errorlevel 1 (
    echo Nao foi possivel baixar o Gradle Wrapper.
    echo Execute: flutter create --platforms=android .
    exit /b 1
  )
)

java -classpath "%WRAPPER_JAR%" org.gradle.wrapper.GradleWrapperMain %*
endlocal
