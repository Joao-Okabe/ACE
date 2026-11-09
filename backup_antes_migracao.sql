--
-- PostgreSQL database dump
--

\restrict yO5qPpQtLWf9arJgMtrckG2RbkX06tQMcoNu1RNSxuCozDPY3QcxHNnOdumJ8sY

-- Dumped from database version 17.11
-- Dumped by pg_dump version 17.11

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: atualizar_timestamp(); Type: FUNCTION; Schema: public; Owner: ace
--

CREATE FUNCTION public.atualizar_timestamp() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.atualizado_em = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.atualizar_timestamp() OWNER TO ace;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: aluno; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.aluno (
    cd_aluno integer NOT NULL,
    cd_usuario integer NOT NULL,
    ra character varying(20) NOT NULL,
    data_nascimento date,
    sexo character(1),
    telefone character varying(20),
    cep character varying(9),
    ativo boolean DEFAULT true NOT NULL,
    criado_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    atualizado_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT chk_sexo CHECK (((sexo = ANY (ARRAY['M'::bpchar, 'F'::bpchar, 'O'::bpchar])) OR (sexo IS NULL)))
);


ALTER TABLE public.aluno OWNER TO ace;

--
-- Name: aluno_cd_aluno_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.aluno ALTER COLUMN cd_aluno ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.aluno_cd_aluno_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: classificacao_grupo; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.classificacao_grupo (
    cd_classificacao_grupo integer NOT NULL,
    cd_vinculo_grupo_time integer NOT NULL,
    cd_grupo integer NOT NULL,
    posicao integer NOT NULL,
    atualizado_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT classificacao_grupo_posicao_check CHECK ((posicao > 0))
);


ALTER TABLE public.classificacao_grupo OWNER TO ace;

--
-- Name: classificacao_grupo_cd_classificacao_grupo_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.classificacao_grupo ALTER COLUMN cd_classificacao_grupo ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.classificacao_grupo_cd_classificacao_grupo_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: competicao; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.competicao (
    cd_competicao integer NOT NULL,
    nm_competicao character varying(150) NOT NULL,
    cd_criador integer,
    cd_esporte integer NOT NULL,
    cd_modalidade integer NOT NULL,
    cd_formato integer NOT NULL,
    cd_escola integer,
    inicio_em timestamp without time zone,
    fim_em timestamp without time zone,
    status character varying(30) DEFAULT 'PLANEJADA'::character varying NOT NULL,
    descricao text,
    criado_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT competicao_status_check CHECK (((status)::text = ANY ((ARRAY['PLANEJADA'::character varying, 'INSCRICOES'::character varying, 'EM_ANDAMENTO'::character varying, 'FINALIZADA'::character varying, 'CANCELADA'::character varying])::text[])))
);


ALTER TABLE public.competicao OWNER TO ace;

--
-- Name: competicao_cd_competicao_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.competicao ALTER COLUMN cd_competicao ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.competicao_cd_competicao_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: configuracao_classificacao; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.configuracao_classificacao (
    cd_configuracao_classificacao integer NOT NULL,
    cd_competicao integer NOT NULL,
    nome character varying(100) NOT NULL,
    descricao text
);


ALTER TABLE public.configuracao_classificacao OWNER TO ace;

--
-- Name: configuracao_classificacao_cd_configuracao_classificacao_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.configuracao_classificacao ALTER COLUMN cd_configuracao_classificacao ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.configuracao_classificacao_cd_configuracao_classificacao_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: confronto; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.confronto (
    cd_confronto integer NOT NULL,
    cd_etapa_competicao integer NOT NULL,
    nr_confronto integer NOT NULL,
    nome character varying(100),
    CONSTRAINT confronto_nr_confronto_check CHECK ((nr_confronto > 0))
);


ALTER TABLE public.confronto OWNER TO ace;

--
-- Name: confronto_cd_confronto_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.confronto ALTER COLUMN cd_confronto ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.confronto_cd_confronto_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: confronto_participante; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.confronto_participante (
    cd_confronto integer NOT NULL,
    posicao smallint NOT NULL,
    cd_origem_participante integer NOT NULL,
    CONSTRAINT confronto_participante_posicao_check CHECK ((posicao = ANY (ARRAY[1, 2])))
);


ALTER TABLE public.confronto_participante OWNER TO ace;

--
-- Name: criterio_classificacao; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.criterio_classificacao (
    cd_criterio_classificacao integer NOT NULL,
    cd_configuracao_classificacao integer NOT NULL,
    cd_metrica_classificacao integer NOT NULL,
    ordem integer NOT NULL,
    direcao character varying(4) DEFAULT 'DESC'::character varying NOT NULL,
    CONSTRAINT criterio_classificacao_direcao_check CHECK (((direcao)::text = ANY ((ARRAY['ASC'::character varying, 'DESC'::character varying])::text[]))),
    CONSTRAINT criterio_classificacao_ordem_check CHECK ((ordem > 0))
);


ALTER TABLE public.criterio_classificacao OWNER TO ace;

--
-- Name: criterio_classificacao_cd_criterio_classificacao_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.criterio_classificacao ALTER COLUMN cd_criterio_classificacao ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.criterio_classificacao_cd_criterio_classificacao_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: documentos_aluno; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.documentos_aluno (
    cd_documentos_aluno integer NOT NULL,
    cd_aluno integer NOT NULL,
    rg text,
    comprovante_escolar text,
    atestado_aptidao_fisica text
);


ALTER TABLE public.documentos_aluno OWNER TO ace;

--
-- Name: documentos_aluno_cd_documentos_aluno_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.documentos_aluno ALTER COLUMN cd_documentos_aluno ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.documentos_aluno_cd_documentos_aluno_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: escalacao_integrante; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.escalacao_integrante (
    cd_vinculo_time_integrante integer NOT NULL,
    titular boolean DEFAULT false,
    ativo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.escalacao_integrante OWNER TO ace;

--
-- Name: escola; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.escola (
    cd_escola integer NOT NULL,
    nm_escola character varying(150) NOT NULL,
    telefone character varying(20),
    cep character varying(9),
    numero character varying(20),
    email character varying(150),
    categoria_administrativa character varying(20) NOT NULL,
    path_brasao text,
    criada_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    ativa boolean DEFAULT true NOT NULL,
    CONSTRAINT chk_categoria CHECK (((categoria_administrativa)::text = ANY ((ARRAY['Escola Municipal'::character varying, 'Escola Estadual'::character varying, 'Privada'::character varying])::text[])))
);


ALTER TABLE public.escola OWNER TO ace;

--
-- Name: escola_cd_escola_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.escola ALTER COLUMN cd_escola ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.escola_cd_escola_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: esporte; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.esporte (
    cd_esporte integer NOT NULL,
    nm_esporte character varying(30) NOT NULL,
    ds_esporte text
);


ALTER TABLE public.esporte OWNER TO ace;

--
-- Name: esporte_cd_esporte_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.esporte ALTER COLUMN cd_esporte ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.esporte_cd_esporte_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: estatistica_classificacao; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.estatistica_classificacao (
    cd_classificacao_grupo integer NOT NULL,
    cd_metrica_classificacao integer NOT NULL,
    valor numeric(15,3) DEFAULT 0 NOT NULL
);


ALTER TABLE public.estatistica_classificacao OWNER TO ace;

--
-- Name: etapa_competicao; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.etapa_competicao (
    cd_etapa_competicao integer NOT NULL,
    cd_competicao integer NOT NULL,
    cd_tipo_etapa integer NOT NULL,
    nm_etapa character varying(100) NOT NULL,
    ordem integer NOT NULL,
    descricao text,
    CONSTRAINT etapa_competicao_ordem_check CHECK ((ordem > 0))
);


ALTER TABLE public.etapa_competicao OWNER TO ace;

--
-- Name: etapa_competicao_cd_etapa_competicao_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.etapa_competicao ALTER COLUMN cd_etapa_competicao ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.etapa_competicao_cd_etapa_competicao_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: etapa_partida; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.etapa_partida (
    cd_etapa_partida integer NOT NULL,
    cd_partida integer NOT NULL,
    nr_etapa integer NOT NULL,
    inicio_em timestamp without time zone,
    fim_em timestamp without time zone,
    CONSTRAINT etapa_partida_check CHECK (((fim_em IS NULL) OR (inicio_em IS NULL) OR (fim_em >= inicio_em))),
    CONSTRAINT etapa_partida_nr_etapa_check CHECK ((nr_etapa > 0))
);


ALTER TABLE public.etapa_partida OWNER TO ace;

--
-- Name: etapa_partida_cd_etapa_partida_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.etapa_partida ALTER COLUMN cd_etapa_partida ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.etapa_partida_cd_etapa_partida_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: formato; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.formato (
    cd_formato integer NOT NULL,
    nm_formato character varying(30) NOT NULL,
    ds_formato text
);


ALTER TABLE public.formato OWNER TO ace;

--
-- Name: formato_cd_formato_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.formato ALTER COLUMN cd_formato ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.formato_cd_formato_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: funcao_integrante; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.funcao_integrante (
    cd_funcao_integrante integer NOT NULL,
    cd_esporte integer NOT NULL,
    nm_funcao character varying(100) NOT NULL,
    ds_funcao text
);


ALTER TABLE public.funcao_integrante OWNER TO ace;

--
-- Name: funcao_integrante_cd_funcao_integrante_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.funcao_integrante ALTER COLUMN cd_funcao_integrante ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.funcao_integrante_cd_funcao_integrante_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: grupo_competicao; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.grupo_competicao (
    cd_grupo integer NOT NULL,
    cd_etapa_competicao integer NOT NULL,
    nm_grupo character varying(50) NOT NULL
);


ALTER TABLE public.grupo_competicao OWNER TO ace;

--
-- Name: grupo_competicao_cd_grupo_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.grupo_competicao ALTER COLUMN cd_grupo ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.grupo_competicao_cd_grupo_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: info_partida; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.info_partida (
    cd_partida integer NOT NULL,
    dt_partida date NOT NULL,
    inicio_em timestamp without time zone,
    fim_em timestamp without time zone,
    local_partida character varying(150),
    observacoes text,
    CONSTRAINT info_partida_check CHECK (((fim_em IS NULL) OR (inicio_em IS NULL) OR (fim_em >= inicio_em)))
);


ALTER TABLE public.info_partida OWNER TO ace;

--
-- Name: inscricao_competicao; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.inscricao_competicao (
    cd_inscricao_competicao integer NOT NULL,
    cd_competicao integer NOT NULL,
    dt_inicio_inscricao timestamp without time zone NOT NULL,
    dt_encerramento_inscricao timestamp without time zone,
    CONSTRAINT inscricao_competicao_check CHECK (((dt_encerramento_inscricao IS NULL) OR (dt_encerramento_inscricao >= dt_inicio_inscricao)))
);


ALTER TABLE public.inscricao_competicao OWNER TO ace;

--
-- Name: metrica_classificacao; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.metrica_classificacao (
    cd_metrica_classificacao integer NOT NULL,
    nm_metrica_classificacao character varying(100) NOT NULL,
    descricao text
);


ALTER TABLE public.metrica_classificacao OWNER TO ace;

--
-- Name: metrica_classificacao_cd_metrica_classificacao_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.metrica_classificacao ALTER COLUMN cd_metrica_classificacao ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.metrica_classificacao_cd_metrica_classificacao_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: modalidade; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.modalidade (
    cd_modalidade integer NOT NULL,
    sexo character varying(5) NOT NULL,
    categoria character varying(10) NOT NULL,
    CONSTRAINT modalidade_categoria_check CHECK (((categoria)::text = ANY ((ARRAY['Sub14'::character varying, 'Sub18'::character varying, 'MISTO'::character varying])::text[]))),
    CONSTRAINT modalidade_sexo_check CHECK (((sexo)::text = ANY ((ARRAY['M'::character varying, 'F'::character varying, 'MISTO'::character varying])::text[])))
);


ALTER TABLE public.modalidade OWNER TO ace;

--
-- Name: modalidade_cd_modalidade_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.modalidade ALTER COLUMN cd_modalidade ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.modalidade_cd_modalidade_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: origem_participante_confronto; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.origem_participante_confronto (
    cd_origem_participante integer NOT NULL,
    tipo_origem character varying(30) NOT NULL,
    cd_time integer,
    cd_confronto integer,
    cd_classificacao_grupo integer,
    resultado_confronto character varying(20),
    CONSTRAINT origem_participante_confronto_check CHECK (((((tipo_origem)::text = 'TIME'::text) AND (cd_time IS NOT NULL) AND (cd_confronto IS NULL) AND (cd_classificacao_grupo IS NULL) AND (resultado_confronto IS NULL)) OR (((tipo_origem)::text = 'CONFRONTO'::text) AND (cd_time IS NULL) AND (cd_confronto IS NOT NULL) AND (cd_classificacao_grupo IS NULL) AND (resultado_confronto IS NOT NULL)) OR (((tipo_origem)::text = 'CLASSIFICACAO_GRUPO'::text) AND (cd_time IS NULL) AND (cd_confronto IS NULL) AND (cd_classificacao_grupo IS NOT NULL) AND (resultado_confronto IS NULL)))),
    CONSTRAINT origem_participante_confronto_resultado_confronto_check CHECK (((resultado_confronto IS NULL) OR ((resultado_confronto)::text = ANY ((ARRAY['VENCEDOR'::character varying, 'PERDEDOR'::character varying])::text[])))),
    CONSTRAINT origem_participante_confronto_tipo_origem_check CHECK (((tipo_origem)::text = ANY ((ARRAY['TIME'::character varying, 'CONFRONTO'::character varying, 'CLASSIFICACAO_GRUPO'::character varying])::text[])))
);


ALTER TABLE public.origem_participante_confronto OWNER TO ace;

--
-- Name: origem_participante_confronto_cd_origem_participante_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.origem_participante_confronto ALTER COLUMN cd_origem_participante ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.origem_participante_confronto_cd_origem_participante_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: papel; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.papel (
    cd_papel integer NOT NULL,
    nm_papel character varying(50) NOT NULL
);


ALTER TABLE public.papel OWNER TO ace;

--
-- Name: papel_cd_papel_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.papel ALTER COLUMN cd_papel ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.papel_cd_papel_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: partida; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.partida (
    cd_partida integer NOT NULL,
    cd_competicao integer NOT NULL,
    cd_rodada integer,
    cd_confronto integer,
    status character varying(30) DEFAULT 'AGENDADA'::character varying NOT NULL,
    inicio_agendado timestamp without time zone,
    CONSTRAINT partida_status_check CHECK (((status)::text = ANY ((ARRAY['AGENDADA'::character varying, 'EM_ANDAMENTO'::character varying, 'FINALIZADA'::character varying, 'CANCELADA'::character varying])::text[])))
);


ALTER TABLE public.partida OWNER TO ace;

--
-- Name: partida_cd_partida_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.partida ALTER COLUMN cd_partida ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.partida_cd_partida_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: penalidade; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.penalidade (
    cd_penalidade integer NOT NULL,
    cd_competicao integer NOT NULL,
    cd_partida integer,
    cd_vinculo_time_integrante integer NOT NULL,
    cd_tipo_penalidade integer NOT NULL,
    descricao text,
    valor numeric(10,3),
    aplicado_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    ativo boolean DEFAULT true NOT NULL,
    CONSTRAINT penalidade_valor_check CHECK (((valor IS NULL) OR (valor >= (0)::numeric)))
);


ALTER TABLE public.penalidade OWNER TO ace;

--
-- Name: penalidade_cd_penalidade_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.penalidade ALTER COLUMN cd_penalidade ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.penalidade_cd_penalidade_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: responsavel; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.responsavel (
    cd_responsavel integer NOT NULL,
    cd_usuario integer NOT NULL,
    ativo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.responsavel OWNER TO ace;

--
-- Name: responsavel_cd_responsavel_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.responsavel ALTER COLUMN cd_responsavel ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.responsavel_cd_responsavel_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: resultado_etapa_partida; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.resultado_etapa_partida (
    cd_etapa_partida integer NOT NULL,
    cd_time integer NOT NULL,
    pontuacao integer DEFAULT 0 NOT NULL,
    CONSTRAINT resultado_etapa_partida_pontuacao_check CHECK ((pontuacao >= 0))
);


ALTER TABLE public.resultado_etapa_partida OWNER TO ace;

--
-- Name: rodada_competicao; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.rodada_competicao (
    cd_rodada integer NOT NULL,
    cd_etapa_competicao integer NOT NULL,
    nr_rodada integer NOT NULL,
    nm_rodada character varying(100),
    inicio_em timestamp without time zone,
    fim_em timestamp without time zone,
    status character varying(30) DEFAULT 'PENDENTE'::character varying NOT NULL,
    CONSTRAINT rodada_competicao_check CHECK (((fim_em IS NULL) OR (inicio_em IS NULL) OR (fim_em >= inicio_em))),
    CONSTRAINT rodada_competicao_nr_rodada_check CHECK ((nr_rodada > 0)),
    CONSTRAINT rodada_competicao_status_check CHECK (((status)::text = ANY ((ARRAY['PENDENTE'::character varying, 'EM_ANDAMENTO'::character varying, 'FINALIZADA'::character varying])::text[])))
);


ALTER TABLE public.rodada_competicao OWNER TO ace;

--
-- Name: rodada_competicao_cd_rodada_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.rodada_competicao ALTER COLUMN cd_rodada ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.rodada_competicao_cd_rodada_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: time; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public."time" (
    cd_time integer NOT NULL,
    nm_time character varying(100) NOT NULL,
    cd_esporte integer NOT NULL,
    descricao text,
    principal boolean DEFAULT false NOT NULL,
    path_escudo character varying(255),
    ativo boolean DEFAULT true NOT NULL,
    criado_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    atualizado_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public."time" OWNER TO ace;

--
-- Name: time_cd_time_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public."time" ALTER COLUMN cd_time ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.time_cd_time_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: tipo_etapa; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.tipo_etapa (
    cd_tipo_etapa integer NOT NULL,
    nm_tipo_etapa character varying(100) NOT NULL,
    ds_tipo_etapa text
);


ALTER TABLE public.tipo_etapa OWNER TO ace;

--
-- Name: tipo_etapa_cd_tipo_etapa_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.tipo_etapa ALTER COLUMN cd_tipo_etapa ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.tipo_etapa_cd_tipo_etapa_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: tipo_penalidade; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.tipo_penalidade (
    cd_tipo_penalidade integer NOT NULL,
    nm_tipo_penalidade character varying(100) NOT NULL,
    origem_penalidade character varying(100) NOT NULL,
    descricao text
);


ALTER TABLE public.tipo_penalidade OWNER TO ace;

--
-- Name: tipo_penalidade_cd_tipo_penalidade_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.tipo_penalidade ALTER COLUMN cd_tipo_penalidade ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.tipo_penalidade_cd_tipo_penalidade_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: usuario; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.usuario (
    cd_usuario integer NOT NULL,
    nm_usuario character varying(150) NOT NULL,
    email character varying(150) NOT NULL,
    senha character varying(255) NOT NULL,
    path_ft_usuario character varying,
    ativo boolean DEFAULT true NOT NULL,
    criado_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    atualizado_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.usuario OWNER TO ace;

--
-- Name: usuario_cd_usuario_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.usuario ALTER COLUMN cd_usuario ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.usuario_cd_usuario_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: usuario_papel; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.usuario_papel (
    cd_usuario integer NOT NULL,
    cd_papel integer NOT NULL,
    ativo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.usuario_papel OWNER TO ace;

--
-- Name: vinculo_grupo_time; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.vinculo_grupo_time (
    cd_vinculo_grupo_time integer NOT NULL,
    cd_grupo integer NOT NULL,
    cd_time integer NOT NULL
);


ALTER TABLE public.vinculo_grupo_time OWNER TO ace;

--
-- Name: vinculo_grupo_time_cd_vinculo_grupo_time_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.vinculo_grupo_time ALTER COLUMN cd_vinculo_grupo_time ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.vinculo_grupo_time_cd_vinculo_grupo_time_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: vinculo_inscricao_time; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.vinculo_inscricao_time (
    cd_inscricao_competicao integer NOT NULL,
    cd_time integer NOT NULL,
    inscrito_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    ativo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.vinculo_inscricao_time OWNER TO ace;

--
-- Name: vinculo_partida_time; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.vinculo_partida_time (
    cd_partida integer NOT NULL,
    cd_time integer NOT NULL,
    mandante boolean DEFAULT false NOT NULL
);


ALTER TABLE public.vinculo_partida_time OWNER TO ace;

--
-- Name: vinculo_tecnico_time; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.vinculo_tecnico_time (
    cd_usuario integer NOT NULL,
    cd_time integer NOT NULL,
    ativo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.vinculo_tecnico_time OWNER TO ace;

--
-- Name: vinculo_time_escola; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.vinculo_time_escola (
    cd_time integer NOT NULL,
    cd_escola integer NOT NULL,
    ativo boolean DEFAULT true NOT NULL,
    criado_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.vinculo_time_escola OWNER TO ace;

--
-- Name: vinculo_time_integrante; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.vinculo_time_integrante (
    cd_vinculo_time_integrante integer NOT NULL,
    cd_time integer NOT NULL,
    cd_usuario integer NOT NULL,
    cd_funcao_integrante integer,
    numero_camisa integer,
    capitao boolean DEFAULT false NOT NULL,
    ativo boolean DEFAULT true NOT NULL,
    CONSTRAINT vinculo_time_integrante_numero_camisa_check CHECK (((numero_camisa IS NULL) OR (numero_camisa > 0)))
);


ALTER TABLE public.vinculo_time_integrante OWNER TO ace;

--
-- Name: vinculo_time_integrante_cd_vinculo_time_integrante_seq; Type: SEQUENCE; Schema: public; Owner: ace
--

ALTER TABLE public.vinculo_time_integrante ALTER COLUMN cd_vinculo_time_integrante ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.vinculo_time_integrante_cd_vinculo_time_integrante_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: vinculo_time_responsavel; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.vinculo_time_responsavel (
    cd_time integer NOT NULL,
    cd_responsavel integer NOT NULL,
    ativo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.vinculo_time_responsavel OWNER TO ace;

--
-- Name: vinculo_usuario_escola; Type: TABLE; Schema: public; Owner: ace
--

CREATE TABLE public.vinculo_usuario_escola (
    cd_usuario integer NOT NULL,
    cd_escola integer NOT NULL,
    cd_papel integer NOT NULL,
    ativo boolean DEFAULT true NOT NULL,
    criado_em timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.vinculo_usuario_escola OWNER TO ace;

--
-- Data for Name: aluno; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.aluno (cd_aluno, cd_usuario, ra, data_nascimento, sexo, telefone, cep, ativo, criado_em, atualizado_em) FROM stdin;
9	19	000009	2008-02-09	M	(13) 9999-9998	11740-000	t	2026-09-23 16:46:27.961097	2026-09-23 16:46:27.961097
10	20	000010	2008-01-10	F	(13) 9999-9999	11740-000	t	2026-09-23 16:46:27.961097	2026-09-23 16:46:27.961097
\.


--
-- Data for Name: classificacao_grupo; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.classificacao_grupo (cd_classificacao_grupo, cd_vinculo_grupo_time, cd_grupo, posicao, atualizado_em) FROM stdin;
\.


--
-- Data for Name: competicao; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.competicao (cd_competicao, nm_competicao, cd_criador, cd_esporte, cd_modalidade, cd_formato, cd_escola, inicio_em, fim_em, status, descricao, criado_em) FROM stdin;
1	min	21	5	8	1	1	2026-09-09 00:00:00	2026-09-16 00:00:00	PLANEJADA	\N	2026-09-23 17:23:41.809433
\.


--
-- Data for Name: configuracao_classificacao; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.configuracao_classificacao (cd_configuracao_classificacao, cd_competicao, nome, descricao) FROM stdin;
\.


--
-- Data for Name: confronto; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.confronto (cd_confronto, cd_etapa_competicao, nr_confronto, nome) FROM stdin;
\.


--
-- Data for Name: confronto_participante; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.confronto_participante (cd_confronto, posicao, cd_origem_participante) FROM stdin;
\.


--
-- Data for Name: criterio_classificacao; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.criterio_classificacao (cd_criterio_classificacao, cd_configuracao_classificacao, cd_metrica_classificacao, ordem, direcao) FROM stdin;
\.


--
-- Data for Name: documentos_aluno; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.documentos_aluno (cd_documentos_aluno, cd_aluno, rg, comprovante_escolar, atestado_aptidao_fisica) FROM stdin;
\.


--
-- Data for Name: escalacao_integrante; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.escalacao_integrante (cd_vinculo_time_integrante, titular, ativo) FROM stdin;
\.


--
-- Data for Name: escola; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.escola (cd_escola, nm_escola, telefone, cep, numero, email, categoria_administrativa, path_brasao, criada_em, ativa) FROM stdin;
2	Profª Silvia R. S. Marasca	(13) 3426-5826	11740-004	241	\N	Escola Municipal	\N	2026-09-23 16:46:27.783573	t
3	Benedito Calixto	(13) 3422-3289	11740-054	180	\N	Escola Estadual	\N	2026-09-23 16:46:27.783573	t
4	Colégio Belas Artes	(13) 3422-6969	11746-694	908	\N	Privada	\N	2026-09-23 16:46:27.783573	t
5	Colégio Evolução Objetivo	(13) 99740-5478	11746-086	143	\N	Privada	\N	2026-09-23 16:46:27.783573	t
6	Leonor Mendes de Barros	(13) 3426-3837	11740-204	180	\N	Escola Municipal	\N	2026-09-23 16:46:27.783573	t
1	Etec de Itanhaém	(13) 3426-4926	11740-976	1431	\N	Escola Estadual	/uploads/IMG_6ab58af2614503.39939213.png	2026-09-23 16:46:27.783573	t
\.


--
-- Data for Name: esporte; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.esporte (cd_esporte, nm_esporte, ds_esporte) FROM stdin;
1	Xadrez	Jogo de tabuleiro de estratégia disputado entre dois jogadores, cujo objetivo é capturar o rei adversário.
2	Futsal	Futebol adaptado para quadras cobertas com 5 jogadores de cada lado e dinâmica de jogo acelerada.
3	Basquete	Esporte dinâmico jogado em quadra onde duas equipes buscam marcar pontos passando a bola por uma cesta elevada a 3,05m do chão.
4	Tênis de Mesa	Esporte de raquete disputado sobre uma mesa dividida por uma rede, exigindo reflexos rápidos e efeito na bolinha.
5	Handebol	Jogo coletivo e de contato intenso em que os jogadores usam as mãos para passar a bola e arremessá-la ao gol adversário.
6	Natação	Esporte aquático focado em percorrer uma determinada distância na piscina no menor tempo possível.
7	Judô	Arte marcial japonesa focada em projeções, imobilizações e chaves para subjugar o oponente.
8	Queimada	Jogo recreativo e esportivo de equipe cujo objetivo é eliminar os integrantes do time adversário acertando-os com a bola.
9	Vôlei	Esporte de rede disputado por duas equipes que usam as mãos e braços para passar a bola por cima da rede, buscando fazê-la tocar o chão da quadra adversária.
\.


--
-- Data for Name: estatistica_classificacao; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.estatistica_classificacao (cd_classificacao_grupo, cd_metrica_classificacao, valor) FROM stdin;
\.


--
-- Data for Name: etapa_competicao; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.etapa_competicao (cd_etapa_competicao, cd_competicao, cd_tipo_etapa, nm_etapa, ordem, descricao) FROM stdin;
\.


--
-- Data for Name: etapa_partida; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.etapa_partida (cd_etapa_partida, cd_partida, nr_etapa, inicio_em, fim_em) FROM stdin;
\.


--
-- Data for Name: formato; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.formato (cd_formato, nm_formato, ds_formato) FROM stdin;
1	Eliminatória Simples	Funciona por meio de confrontos diretos onde quem perde o jogo é eliminado da competição, enquanto quem vence avança para a próxima etapa
2	Eliminatória Dupla	O formato de eliminatórias duplas (ou dupla eliminação) é um sistema de torneio em que uma equipe ou jogador só é eliminado após sofrer a sua segunda derrota
3	Fase de Grupo + Eliminatória	O formato de fase de grupos padrão no futebol divide as equipes em chaves (grupos) onde todos os times jogam entre si dentro de seus respectivos grupos, os com mais pontos seguirão para a fase de Eliminatória
4	Sistema Suiço	O sistema suíço é um formato de competição não eliminatório em que os participantes disputam um número fixo de rodadas e enfrentam oponentes com campanhas semelhantes
\.


--
-- Data for Name: funcao_integrante; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.funcao_integrante (cd_funcao_integrante, cd_esporte, nm_funcao, ds_funcao) FROM stdin;
25	2	Goleiro	Defensor principal
26	2	Fixo	Defensor central
27	2	Alas Esquerdo	Atuam pelas respectivas laterais
28	2	Alas Direito	Atuam pelas respectivas laterais
29	2	Pivô	Atacante de referência
30	3	Armador	Organiza o jogo
31	3	Ala-Armador	Arremessos de três pontos e infiltrações rápidas na defesa.
32	3	Ala	Jogador Versátil
33	3	Ala-Pivô	Disputar rebotes, bloquear adversários e pontuar perto ou a média distância da cesta.
34	3	Pivô	Joga próximo à cesta
35	5	Goleiro	Defensor principal
36	5	Ponta Esquerda	Atua bem aberto, no canto da quadra, próximo à linha de fundo e à linha lateral.
37	5	Ponta Direita	Atua bem aberto, no canto da quadra, próximo à linha de fundo e à linha lateral.
38	5	Armador Lateral Esquerdo	Atua na região intermediária, entre o armador central e o ponta.
39	5	Armador Lateral Direito	Atua na região intermediária, entre o armador central e o ponta.
40	5	Armador Central	Responsável por organizar e comandar as jogadas de ataque no centro da quadra
41	5	Pivô	jogador de ataque que atua bem no meio da defesa adversária, geralmente na linha dos 6 metros, com a função de abrir espaços e finalizar
42	8	Levantador	Organiza o ataque
43	8	Ponteiro A	Atacam pelas pontas e ajudam na recepção
44	8	Ponteiro B	Atacam pelas pontas e ajudam na recepção
45	9	Oposto A	Principais pontuadores de ataque
46	9	Oposto B	Principais pontuadores de ataque
47	9	Central	Ataca pelo meio e lidera o bloqueio
48	9	Libero	Especialista defensivo que não ataca nem saca
\.


--
-- Data for Name: grupo_competicao; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.grupo_competicao (cd_grupo, cd_etapa_competicao, nm_grupo) FROM stdin;
\.


--
-- Data for Name: info_partida; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.info_partida (cd_partida, dt_partida, inicio_em, fim_em, local_partida, observacoes) FROM stdin;
\.


--
-- Data for Name: inscricao_competicao; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.inscricao_competicao (cd_inscricao_competicao, cd_competicao, dt_inicio_inscricao, dt_encerramento_inscricao) FROM stdin;
1	1	2026-09-09 00:00:00	2026-09-16 00:00:00
2	1	2026-09-09 00:00:00	2026-09-16 00:00:00
\.


--
-- Data for Name: metrica_classificacao; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.metrica_classificacao (cd_metrica_classificacao, nm_metrica_classificacao, descricao) FROM stdin;
\.


--
-- Data for Name: modalidade; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.modalidade (cd_modalidade, sexo, categoria) FROM stdin;
1	M	Sub14
2	F	Sub14
3	MISTO	Sub14
4	M	Sub18
5	F	Sub18
6	MISTO	Sub18
7	M	MISTO
8	F	MISTO
9	MISTO	MISTO
\.


--
-- Data for Name: origem_participante_confronto; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.origem_participante_confronto (cd_origem_participante, tipo_origem, cd_time, cd_confronto, cd_classificacao_grupo, resultado_confronto) FROM stdin;
\.


--
-- Data for Name: papel; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.papel (cd_papel, nm_papel) FROM stdin;
1	ADM
2	DIR
3	CRD
4	PRF
5	ARB
6	AGR
7	ALU
8	VIS
\.


--
-- Data for Name: partida; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.partida (cd_partida, cd_competicao, cd_rodada, cd_confronto, status, inicio_agendado) FROM stdin;
\.


--
-- Data for Name: penalidade; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.penalidade (cd_penalidade, cd_competicao, cd_partida, cd_vinculo_time_integrante, cd_tipo_penalidade, descricao, valor, aplicado_em, ativo) FROM stdin;
\.


--
-- Data for Name: responsavel; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.responsavel (cd_responsavel, cd_usuario, ativo) FROM stdin;
2	3	t
\.


--
-- Data for Name: resultado_etapa_partida; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.resultado_etapa_partida (cd_etapa_partida, cd_time, pontuacao) FROM stdin;
\.


--
-- Data for Name: rodada_competicao; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.rodada_competicao (cd_rodada, cd_etapa_competicao, nr_rodada, nm_rodada, inicio_em, fim_em, status) FROM stdin;
\.


--
-- Data for Name: time; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public."time" (cd_time, nm_time, cd_esporte, descricao, principal, path_escudo, ativo, criado_em, atualizado_em) FROM stdin;
1	loj	7	\N	f	/uploads/IMG_6ab40f1034a2b1.27915243.png	t	2026-09-23 17:40:32.215309	2026-09-23 17:40:32.215309
2	3MIN	2	\N	f	\N	t	2026-09-23 17:58:45.661091	2026-09-23 17:58:45.661091
3	fgfgfghfhgfghfg	2	\N	f	\N	t	2026-09-23 18:21:44.019402	2026-09-23 18:21:44.019402
\.


--
-- Data for Name: tipo_etapa; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.tipo_etapa (cd_tipo_etapa, nm_tipo_etapa, ds_tipo_etapa) FROM stdin;
\.


--
-- Data for Name: tipo_penalidade; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.tipo_penalidade (cd_tipo_penalidade, nm_tipo_penalidade, origem_penalidade, descricao) FROM stdin;
\.


--
-- Data for Name: usuario; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.usuario (cd_usuario, nm_usuario, email, senha, path_ft_usuario, ativo, criado_em, atualizado_em) FROM stdin;
1	Admilson Adéemi	adeemi@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
2	Milena Biazzus da Silva Ribeiro	biazzus@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
3	Rodrigo Ferraz	ferraz@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
4	Willians Souza de Oliveira	oliveira@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
5	Arbitrelson Arbitraís	arbitrais@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
6	Yasmin da Silva Santos	silva@aluno.cps.sp.gov.br	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
7	João Antonio Okabe Van Berghem	berghem@aluno.cps.sp.gov.br	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
8	Renato Leandro	leandro@aluno.cps.sp.gov.br	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
9	Gustavo Willians Trigo	trigo@aluno.cps.sp.gov.br	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
10	Nádia Cristina Okabe	okabe@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
12	Aluno 2	aluno2@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
13	Aluno 3	aluno3@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
14	Aluno 4	aluno4@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
15	Aluno 5	aluno5@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
16	Aluno 6	aluno6@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
17	Aluno 7	aluno7@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
18	Aluno 8	aluno8@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
19	Aluno 9	aluno9@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
20	Aluno 10	aluno10@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	\N	t	2026-09-23 16:46:27.60745	2026-09-23 16:46:27.60745
21	Clara Teste	clara@teste.com	$2y$12$OR2b7YqkhoecX26LuCSGQuosDtz19veoj5MDLRXq5YjLsBWoFbL5q	\N	t	2026-09-23 16:57:35.61002	2026-09-23 16:57:35.61002
22	Diretor Teste	diretor@teste.com	$2y$12$OR2b7YqkhoecX26LuCSGQuosDtz19veoj5MDLRXq5YjLsBWoFbL5q	\N	t	2026-09-23 18:09:34.828704	2026-09-23 18:09:34.828704
23	Tecnico Teste	tecnico@teste.com	$2y$12$OR2b7YqkhoecX26LuCSGQuosDtz19veoj5MDLRXq5YjLsBWoFbL5q	\N	t	2026-09-23 18:17:23.542567	2026-09-23 18:17:23.542567
11	Aluno 1	aluno1@gmail.com	$2a$12$uIfCzkWNVpU56aDwp9qyiuVnC6U2osMlK1o0x8VtIm9OAvAOwr1LC	/uploads/IMG_6ab597ff79ed39.63480719.png	t	2026-09-23 16:46:27.60745	2026-09-24 21:37:03.499195
\.


--
-- Data for Name: usuario_papel; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.usuario_papel (cd_usuario, cd_papel, ativo) FROM stdin;
1	1	t
8	8	t
21	1	t
22	2	t
23	4	t
\.


--
-- Data for Name: vinculo_grupo_time; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.vinculo_grupo_time (cd_vinculo_grupo_time, cd_grupo, cd_time) FROM stdin;
\.


--
-- Data for Name: vinculo_inscricao_time; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.vinculo_inscricao_time (cd_inscricao_competicao, cd_time, inscrito_em, ativo) FROM stdin;
\.


--
-- Data for Name: vinculo_partida_time; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.vinculo_partida_time (cd_partida, cd_time, mandante) FROM stdin;
\.


--
-- Data for Name: vinculo_tecnico_time; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.vinculo_tecnico_time (cd_usuario, cd_time, ativo) FROM stdin;
1	2	t
7	2	t
23	1	t
11	1	t
\.


--
-- Data for Name: vinculo_time_escola; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.vinculo_time_escola (cd_time, cd_escola, ativo, criado_em) FROM stdin;
1	1	t	2026-09-23 17:40:32.215309
2	1	t	2026-09-23 17:58:45.661091
3	1	t	2026-09-23 18:21:44.019402
\.


--
-- Data for Name: vinculo_time_integrante; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.vinculo_time_integrante (cd_vinculo_time_integrante, cd_time, cd_usuario, cd_funcao_integrante, numero_camisa, capitao, ativo) FROM stdin;
3	3	20	26	1	t	t
1	3	13	27	10	f	t
\.


--
-- Data for Name: vinculo_time_responsavel; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.vinculo_time_responsavel (cd_time, cd_responsavel, ativo) FROM stdin;
3	2	t
1	2	t
\.


--
-- Data for Name: vinculo_usuario_escola; Type: TABLE DATA; Schema: public; Owner: ace
--

COPY public.vinculo_usuario_escola (cd_usuario, cd_escola, cd_papel, ativo, criado_em) FROM stdin;
11	1	7	t	2026-09-23 16:46:27.869302
12	1	7	t	2026-09-23 16:46:27.869302
13	1	7	t	2026-09-23 16:46:27.869302
14	1	7	t	2026-09-23 16:46:27.869302
15	1	7	t	2026-09-23 16:46:27.869302
16	1	7	t	2026-09-23 16:46:27.869302
17	1	7	t	2026-09-23 16:46:27.869302
18	1	7	t	2026-09-23 16:46:27.869302
19	1	7	t	2026-09-23 16:46:27.869302
20	1	7	t	2026-09-23 16:46:27.869302
2	1	2	t	2026-09-23 16:46:27.869302
3	1	3	t	2026-09-23 16:46:27.869302
4	1	4	t	2026-09-23 16:46:27.869302
22	1	2	t	2026-09-23 18:09:34.828704
\.


--
-- Name: aluno_cd_aluno_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.aluno_cd_aluno_seq', 10, true);


--
-- Name: classificacao_grupo_cd_classificacao_grupo_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.classificacao_grupo_cd_classificacao_grupo_seq', 1, false);


--
-- Name: competicao_cd_competicao_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.competicao_cd_competicao_seq', 1, true);


--
-- Name: configuracao_classificacao_cd_configuracao_classificacao_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.configuracao_classificacao_cd_configuracao_classificacao_seq', 1, false);


--
-- Name: confronto_cd_confronto_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.confronto_cd_confronto_seq', 1, false);


--
-- Name: criterio_classificacao_cd_criterio_classificacao_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.criterio_classificacao_cd_criterio_classificacao_seq', 1, false);


--
-- Name: documentos_aluno_cd_documentos_aluno_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.documentos_aluno_cd_documentos_aluno_seq', 1, false);


--
-- Name: escola_cd_escola_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.escola_cd_escola_seq', 6, true);


--
-- Name: esporte_cd_esporte_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.esporte_cd_esporte_seq', 9, true);


--
-- Name: etapa_competicao_cd_etapa_competicao_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.etapa_competicao_cd_etapa_competicao_seq', 1, false);


--
-- Name: etapa_partida_cd_etapa_partida_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.etapa_partida_cd_etapa_partida_seq', 1, false);


--
-- Name: formato_cd_formato_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.formato_cd_formato_seq', 4, true);


--
-- Name: funcao_integrante_cd_funcao_integrante_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.funcao_integrante_cd_funcao_integrante_seq', 48, true);


--
-- Name: grupo_competicao_cd_grupo_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.grupo_competicao_cd_grupo_seq', 1, false);


--
-- Name: metrica_classificacao_cd_metrica_classificacao_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.metrica_classificacao_cd_metrica_classificacao_seq', 1, false);


--
-- Name: modalidade_cd_modalidade_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.modalidade_cd_modalidade_seq', 9, true);


--
-- Name: origem_participante_confronto_cd_origem_participante_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.origem_participante_confronto_cd_origem_participante_seq', 1, false);


--
-- Name: papel_cd_papel_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.papel_cd_papel_seq', 8, true);


--
-- Name: partida_cd_partida_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.partida_cd_partida_seq', 1, false);


--
-- Name: penalidade_cd_penalidade_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.penalidade_cd_penalidade_seq', 1, false);


--
-- Name: responsavel_cd_responsavel_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.responsavel_cd_responsavel_seq', 2, true);


--
-- Name: rodada_competicao_cd_rodada_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.rodada_competicao_cd_rodada_seq', 1, false);


--
-- Name: time_cd_time_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.time_cd_time_seq', 3, true);


--
-- Name: tipo_etapa_cd_tipo_etapa_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.tipo_etapa_cd_tipo_etapa_seq', 1, false);


--
-- Name: tipo_penalidade_cd_tipo_penalidade_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.tipo_penalidade_cd_tipo_penalidade_seq', 1, false);


--
-- Name: usuario_cd_usuario_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.usuario_cd_usuario_seq', 23, true);


--
-- Name: vinculo_grupo_time_cd_vinculo_grupo_time_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.vinculo_grupo_time_cd_vinculo_grupo_time_seq', 1, false);


--
-- Name: vinculo_time_integrante_cd_vinculo_time_integrante_seq; Type: SEQUENCE SET; Schema: public; Owner: ace
--

SELECT pg_catalog.setval('public.vinculo_time_integrante_cd_vinculo_time_integrante_seq', 3, true);


--
-- Name: aluno aluno_cd_usuario_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.aluno
    ADD CONSTRAINT aluno_cd_usuario_key UNIQUE (cd_usuario);


--
-- Name: aluno aluno_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.aluno
    ADD CONSTRAINT aluno_pkey PRIMARY KEY (cd_aluno);


--
-- Name: aluno aluno_ra_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.aluno
    ADD CONSTRAINT aluno_ra_key UNIQUE (ra);


--
-- Name: classificacao_grupo classificacao_grupo_cd_grupo_posicao_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.classificacao_grupo
    ADD CONSTRAINT classificacao_grupo_cd_grupo_posicao_key UNIQUE (cd_grupo, posicao);


--
-- Name: classificacao_grupo classificacao_grupo_cd_vinculo_grupo_time_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.classificacao_grupo
    ADD CONSTRAINT classificacao_grupo_cd_vinculo_grupo_time_key UNIQUE (cd_vinculo_grupo_time);


--
-- Name: classificacao_grupo classificacao_grupo_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.classificacao_grupo
    ADD CONSTRAINT classificacao_grupo_pkey PRIMARY KEY (cd_classificacao_grupo);


--
-- Name: competicao competicao_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.competicao
    ADD CONSTRAINT competicao_pkey PRIMARY KEY (cd_competicao);


--
-- Name: configuracao_classificacao configuracao_classificacao_cd_competicao_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.configuracao_classificacao
    ADD CONSTRAINT configuracao_classificacao_cd_competicao_key UNIQUE (cd_competicao);


--
-- Name: configuracao_classificacao configuracao_classificacao_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.configuracao_classificacao
    ADD CONSTRAINT configuracao_classificacao_pkey PRIMARY KEY (cd_configuracao_classificacao);


--
-- Name: confronto confronto_cd_etapa_competicao_nr_confronto_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.confronto
    ADD CONSTRAINT confronto_cd_etapa_competicao_nr_confronto_key UNIQUE (cd_etapa_competicao, nr_confronto);


--
-- Name: confronto_participante confronto_participante_cd_confronto_cd_origem_participante_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.confronto_participante
    ADD CONSTRAINT confronto_participante_cd_confronto_cd_origem_participante_key UNIQUE (cd_confronto, cd_origem_participante);


--
-- Name: confronto_participante confronto_participante_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.confronto_participante
    ADD CONSTRAINT confronto_participante_pkey PRIMARY KEY (cd_confronto, posicao);


--
-- Name: confronto confronto_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.confronto
    ADD CONSTRAINT confronto_pkey PRIMARY KEY (cd_confronto);


--
-- Name: criterio_classificacao criterio_classificacao_cd_configuracao_classificacao_cd_met_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.criterio_classificacao
    ADD CONSTRAINT criterio_classificacao_cd_configuracao_classificacao_cd_met_key UNIQUE (cd_configuracao_classificacao, cd_metrica_classificacao);


--
-- Name: criterio_classificacao criterio_classificacao_cd_configuracao_classificacao_ordem_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.criterio_classificacao
    ADD CONSTRAINT criterio_classificacao_cd_configuracao_classificacao_ordem_key UNIQUE (cd_configuracao_classificacao, ordem);


--
-- Name: criterio_classificacao criterio_classificacao_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.criterio_classificacao
    ADD CONSTRAINT criterio_classificacao_pkey PRIMARY KEY (cd_criterio_classificacao);


--
-- Name: documentos_aluno documentos_aluno_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.documentos_aluno
    ADD CONSTRAINT documentos_aluno_pkey PRIMARY KEY (cd_documentos_aluno);


--
-- Name: escola escola_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.escola
    ADD CONSTRAINT escola_pkey PRIMARY KEY (cd_escola);


--
-- Name: esporte esporte_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.esporte
    ADD CONSTRAINT esporte_pkey PRIMARY KEY (cd_esporte);


--
-- Name: estatistica_classificacao estatistica_classificacao_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.estatistica_classificacao
    ADD CONSTRAINT estatistica_classificacao_pkey PRIMARY KEY (cd_classificacao_grupo, cd_metrica_classificacao);


--
-- Name: etapa_competicao etapa_competicao_cd_competicao_ordem_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.etapa_competicao
    ADD CONSTRAINT etapa_competicao_cd_competicao_ordem_key UNIQUE (cd_competicao, ordem);


--
-- Name: etapa_competicao etapa_competicao_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.etapa_competicao
    ADD CONSTRAINT etapa_competicao_pkey PRIMARY KEY (cd_etapa_competicao);


--
-- Name: etapa_partida etapa_partida_cd_partida_nr_etapa_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.etapa_partida
    ADD CONSTRAINT etapa_partida_cd_partida_nr_etapa_key UNIQUE (cd_partida, nr_etapa);


--
-- Name: etapa_partida etapa_partida_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.etapa_partida
    ADD CONSTRAINT etapa_partida_pkey PRIMARY KEY (cd_etapa_partida);


--
-- Name: formato formato_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.formato
    ADD CONSTRAINT formato_pkey PRIMARY KEY (cd_formato);


--
-- Name: funcao_integrante funcao_integrante_cd_esporte_nm_funcao_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.funcao_integrante
    ADD CONSTRAINT funcao_integrante_cd_esporte_nm_funcao_key UNIQUE (cd_esporte, nm_funcao);


--
-- Name: funcao_integrante funcao_integrante_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.funcao_integrante
    ADD CONSTRAINT funcao_integrante_pkey PRIMARY KEY (cd_funcao_integrante);


--
-- Name: grupo_competicao grupo_competicao_cd_etapa_competicao_nm_grupo_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.grupo_competicao
    ADD CONSTRAINT grupo_competicao_cd_etapa_competicao_nm_grupo_key UNIQUE (cd_etapa_competicao, nm_grupo);


--
-- Name: grupo_competicao grupo_competicao_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.grupo_competicao
    ADD CONSTRAINT grupo_competicao_pkey PRIMARY KEY (cd_grupo);


--
-- Name: info_partida info_partida_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.info_partida
    ADD CONSTRAINT info_partida_pkey PRIMARY KEY (cd_partida);


--
-- Name: inscricao_competicao inscricao_competicao_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.inscricao_competicao
    ADD CONSTRAINT inscricao_competicao_pkey PRIMARY KEY (cd_inscricao_competicao);


--
-- Name: metrica_classificacao metrica_classificacao_nm_metrica_classificacao_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.metrica_classificacao
    ADD CONSTRAINT metrica_classificacao_nm_metrica_classificacao_key UNIQUE (nm_metrica_classificacao);


--
-- Name: metrica_classificacao metrica_classificacao_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.metrica_classificacao
    ADD CONSTRAINT metrica_classificacao_pkey PRIMARY KEY (cd_metrica_classificacao);


--
-- Name: modalidade modalidade_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.modalidade
    ADD CONSTRAINT modalidade_pkey PRIMARY KEY (cd_modalidade);


--
-- Name: modalidade modalidade_sexo_categoria_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.modalidade
    ADD CONSTRAINT modalidade_sexo_categoria_key UNIQUE (sexo, categoria);


--
-- Name: origem_participante_confronto origem_participante_confronto_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.origem_participante_confronto
    ADD CONSTRAINT origem_participante_confronto_pkey PRIMARY KEY (cd_origem_participante);


--
-- Name: papel papel_nm_papel_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.papel
    ADD CONSTRAINT papel_nm_papel_key UNIQUE (nm_papel);


--
-- Name: papel papel_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.papel
    ADD CONSTRAINT papel_pkey PRIMARY KEY (cd_papel);


--
-- Name: partida partida_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.partida
    ADD CONSTRAINT partida_pkey PRIMARY KEY (cd_partida);


--
-- Name: penalidade penalidade_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.penalidade
    ADD CONSTRAINT penalidade_pkey PRIMARY KEY (cd_penalidade);


--
-- Name: responsavel responsavel_cd_usuario_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.responsavel
    ADD CONSTRAINT responsavel_cd_usuario_key UNIQUE (cd_usuario);


--
-- Name: responsavel responsavel_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.responsavel
    ADD CONSTRAINT responsavel_pkey PRIMARY KEY (cd_responsavel);


--
-- Name: resultado_etapa_partida resultado_etapa_partida_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.resultado_etapa_partida
    ADD CONSTRAINT resultado_etapa_partida_pkey PRIMARY KEY (cd_etapa_partida, cd_time);


--
-- Name: rodada_competicao rodada_competicao_cd_etapa_competicao_nr_rodada_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.rodada_competicao
    ADD CONSTRAINT rodada_competicao_cd_etapa_competicao_nr_rodada_key UNIQUE (cd_etapa_competicao, nr_rodada);


--
-- Name: rodada_competicao rodada_competicao_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.rodada_competicao
    ADD CONSTRAINT rodada_competicao_pkey PRIMARY KEY (cd_rodada);


--
-- Name: time time_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public."time"
    ADD CONSTRAINT time_pkey PRIMARY KEY (cd_time);


--
-- Name: tipo_etapa tipo_etapa_nm_tipo_etapa_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.tipo_etapa
    ADD CONSTRAINT tipo_etapa_nm_tipo_etapa_key UNIQUE (nm_tipo_etapa);


--
-- Name: tipo_etapa tipo_etapa_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.tipo_etapa
    ADD CONSTRAINT tipo_etapa_pkey PRIMARY KEY (cd_tipo_etapa);


--
-- Name: tipo_penalidade tipo_penalidade_nm_tipo_penalidade_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.tipo_penalidade
    ADD CONSTRAINT tipo_penalidade_nm_tipo_penalidade_key UNIQUE (nm_tipo_penalidade);


--
-- Name: tipo_penalidade tipo_penalidade_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.tipo_penalidade
    ADD CONSTRAINT tipo_penalidade_pkey PRIMARY KEY (cd_tipo_penalidade);


--
-- Name: usuario usuario_email_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_email_key UNIQUE (email);


--
-- Name: usuario_papel usuario_papel_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.usuario_papel
    ADD CONSTRAINT usuario_papel_pkey PRIMARY KEY (cd_usuario, cd_papel);


--
-- Name: usuario usuario_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_pkey PRIMARY KEY (cd_usuario);


--
-- Name: vinculo_grupo_time vinculo_grupo_time_cd_grupo_cd_time_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_grupo_time
    ADD CONSTRAINT vinculo_grupo_time_cd_grupo_cd_time_key UNIQUE (cd_grupo, cd_time);


--
-- Name: vinculo_grupo_time vinculo_grupo_time_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_grupo_time
    ADD CONSTRAINT vinculo_grupo_time_pkey PRIMARY KEY (cd_vinculo_grupo_time);


--
-- Name: vinculo_inscricao_time vinculo_inscricao_time_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_inscricao_time
    ADD CONSTRAINT vinculo_inscricao_time_pkey PRIMARY KEY (cd_inscricao_competicao, cd_time);


--
-- Name: vinculo_partida_time vinculo_partida_time_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_partida_time
    ADD CONSTRAINT vinculo_partida_time_pkey PRIMARY KEY (cd_partida, cd_time);


--
-- Name: vinculo_tecnico_time vinculo_tecnico_time_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_tecnico_time
    ADD CONSTRAINT vinculo_tecnico_time_pkey PRIMARY KEY (cd_usuario, cd_time);


--
-- Name: vinculo_time_escola vinculo_time_escola_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_time_escola
    ADD CONSTRAINT vinculo_time_escola_pkey PRIMARY KEY (cd_time, cd_escola);


--
-- Name: vinculo_time_integrante vinculo_time_integrante_cd_time_cd_usuario_key; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_time_integrante
    ADD CONSTRAINT vinculo_time_integrante_cd_time_cd_usuario_key UNIQUE (cd_time, cd_usuario);


--
-- Name: vinculo_time_integrante vinculo_time_integrante_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_time_integrante
    ADD CONSTRAINT vinculo_time_integrante_pkey PRIMARY KEY (cd_vinculo_time_integrante);


--
-- Name: vinculo_time_responsavel vinculo_time_responsavel_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_time_responsavel
    ADD CONSTRAINT vinculo_time_responsavel_pkey PRIMARY KEY (cd_time, cd_responsavel);


--
-- Name: vinculo_usuario_escola vinculo_usuario_escola_pkey; Type: CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_usuario_escola
    ADD CONSTRAINT vinculo_usuario_escola_pkey PRIMARY KEY (cd_usuario, cd_escola, cd_papel);


--
-- Name: aluno trg_aluno_timestamp; Type: TRIGGER; Schema: public; Owner: ace
--

CREATE TRIGGER trg_aluno_timestamp BEFORE UPDATE ON public.aluno FOR EACH ROW EXECUTE FUNCTION public.atualizar_timestamp();


--
-- Name: time trg_time_timestamp; Type: TRIGGER; Schema: public; Owner: ace
--

CREATE TRIGGER trg_time_timestamp BEFORE UPDATE ON public."time" FOR EACH ROW EXECUTE FUNCTION public.atualizar_timestamp();


--
-- Name: usuario trg_usuario_timestamp; Type: TRIGGER; Schema: public; Owner: ace
--

CREATE TRIGGER trg_usuario_timestamp BEFORE UPDATE ON public.usuario FOR EACH ROW EXECUTE FUNCTION public.atualizar_timestamp();


--
-- Name: classificacao_grupo classificacao_grupo_cd_grupo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.classificacao_grupo
    ADD CONSTRAINT classificacao_grupo_cd_grupo_fkey FOREIGN KEY (cd_grupo) REFERENCES public.grupo_competicao(cd_grupo) ON DELETE CASCADE;


--
-- Name: classificacao_grupo classificacao_grupo_cd_vinculo_grupo_time_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.classificacao_grupo
    ADD CONSTRAINT classificacao_grupo_cd_vinculo_grupo_time_fkey FOREIGN KEY (cd_vinculo_grupo_time) REFERENCES public.vinculo_grupo_time(cd_vinculo_grupo_time) ON DELETE CASCADE;


--
-- Name: competicao competicao_cd_criador_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.competicao
    ADD CONSTRAINT competicao_cd_criador_fkey FOREIGN KEY (cd_criador) REFERENCES public.usuario(cd_usuario) ON DELETE RESTRICT;


--
-- Name: competicao competicao_cd_escola_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.competicao
    ADD CONSTRAINT competicao_cd_escola_fkey FOREIGN KEY (cd_escola) REFERENCES public.escola(cd_escola) ON DELETE SET NULL;


--
-- Name: competicao competicao_cd_esporte_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.competicao
    ADD CONSTRAINT competicao_cd_esporte_fkey FOREIGN KEY (cd_esporte) REFERENCES public.esporte(cd_esporte) ON DELETE RESTRICT;


--
-- Name: competicao competicao_cd_formato_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.competicao
    ADD CONSTRAINT competicao_cd_formato_fkey FOREIGN KEY (cd_formato) REFERENCES public.formato(cd_formato) ON DELETE RESTRICT;


--
-- Name: competicao competicao_cd_modalidade_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.competicao
    ADD CONSTRAINT competicao_cd_modalidade_fkey FOREIGN KEY (cd_modalidade) REFERENCES public.modalidade(cd_modalidade) ON DELETE RESTRICT;


--
-- Name: configuracao_classificacao configuracao_classificacao_cd_competicao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.configuracao_classificacao
    ADD CONSTRAINT configuracao_classificacao_cd_competicao_fkey FOREIGN KEY (cd_competicao) REFERENCES public.competicao(cd_competicao) ON DELETE CASCADE;


--
-- Name: confronto confronto_cd_etapa_competicao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.confronto
    ADD CONSTRAINT confronto_cd_etapa_competicao_fkey FOREIGN KEY (cd_etapa_competicao) REFERENCES public.etapa_competicao(cd_etapa_competicao) ON DELETE CASCADE;


--
-- Name: confronto_participante confronto_participante_cd_confronto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.confronto_participante
    ADD CONSTRAINT confronto_participante_cd_confronto_fkey FOREIGN KEY (cd_confronto) REFERENCES public.confronto(cd_confronto) ON DELETE CASCADE;


--
-- Name: confronto_participante confronto_participante_cd_origem_participante_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.confronto_participante
    ADD CONSTRAINT confronto_participante_cd_origem_participante_fkey FOREIGN KEY (cd_origem_participante) REFERENCES public.origem_participante_confronto(cd_origem_participante) ON DELETE CASCADE;


--
-- Name: criterio_classificacao criterio_classificacao_cd_configuracao_classificacao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.criterio_classificacao
    ADD CONSTRAINT criterio_classificacao_cd_configuracao_classificacao_fkey FOREIGN KEY (cd_configuracao_classificacao) REFERENCES public.configuracao_classificacao(cd_configuracao_classificacao) ON DELETE CASCADE;


--
-- Name: criterio_classificacao criterio_classificacao_cd_metrica_classificacao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.criterio_classificacao
    ADD CONSTRAINT criterio_classificacao_cd_metrica_classificacao_fkey FOREIGN KEY (cd_metrica_classificacao) REFERENCES public.metrica_classificacao(cd_metrica_classificacao) ON DELETE RESTRICT;


--
-- Name: escalacao_integrante escalacao_integrante_cd_vinculo_time_integrante_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.escalacao_integrante
    ADD CONSTRAINT escalacao_integrante_cd_vinculo_time_integrante_fkey FOREIGN KEY (cd_vinculo_time_integrante) REFERENCES public.vinculo_time_integrante(cd_vinculo_time_integrante) ON DELETE CASCADE;


--
-- Name: estatistica_classificacao estatistica_classificacao_cd_classificacao_grupo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.estatistica_classificacao
    ADD CONSTRAINT estatistica_classificacao_cd_classificacao_grupo_fkey FOREIGN KEY (cd_classificacao_grupo) REFERENCES public.classificacao_grupo(cd_classificacao_grupo) ON DELETE CASCADE;


--
-- Name: estatistica_classificacao estatistica_classificacao_cd_metrica_classificacao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.estatistica_classificacao
    ADD CONSTRAINT estatistica_classificacao_cd_metrica_classificacao_fkey FOREIGN KEY (cd_metrica_classificacao) REFERENCES public.metrica_classificacao(cd_metrica_classificacao) ON DELETE RESTRICT;


--
-- Name: etapa_competicao etapa_competicao_cd_competicao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.etapa_competicao
    ADD CONSTRAINT etapa_competicao_cd_competicao_fkey FOREIGN KEY (cd_competicao) REFERENCES public.competicao(cd_competicao) ON DELETE CASCADE;


--
-- Name: etapa_competicao etapa_competicao_cd_tipo_etapa_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.etapa_competicao
    ADD CONSTRAINT etapa_competicao_cd_tipo_etapa_fkey FOREIGN KEY (cd_tipo_etapa) REFERENCES public.tipo_etapa(cd_tipo_etapa) ON DELETE RESTRICT;


--
-- Name: etapa_partida etapa_partida_cd_partida_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.etapa_partida
    ADD CONSTRAINT etapa_partida_cd_partida_fkey FOREIGN KEY (cd_partida) REFERENCES public.partida(cd_partida) ON DELETE CASCADE;


--
-- Name: aluno fk_aluno_usuario; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.aluno
    ADD CONSTRAINT fk_aluno_usuario FOREIGN KEY (cd_usuario) REFERENCES public.usuario(cd_usuario) ON DELETE CASCADE;


--
-- Name: documentos_aluno fk_cd_aluno; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.documentos_aluno
    ADD CONSTRAINT fk_cd_aluno FOREIGN KEY (cd_aluno) REFERENCES public.aluno(cd_aluno) ON DELETE CASCADE;


--
-- Name: funcao_integrante funcao_integrante_cd_esporte_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.funcao_integrante
    ADD CONSTRAINT funcao_integrante_cd_esporte_fkey FOREIGN KEY (cd_esporte) REFERENCES public.esporte(cd_esporte) ON DELETE CASCADE;


--
-- Name: grupo_competicao grupo_competicao_cd_etapa_competicao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.grupo_competicao
    ADD CONSTRAINT grupo_competicao_cd_etapa_competicao_fkey FOREIGN KEY (cd_etapa_competicao) REFERENCES public.etapa_competicao(cd_etapa_competicao) ON DELETE CASCADE;


--
-- Name: info_partida info_partida_cd_partida_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.info_partida
    ADD CONSTRAINT info_partida_cd_partida_fkey FOREIGN KEY (cd_partida) REFERENCES public.partida(cd_partida) ON DELETE CASCADE;


--
-- Name: inscricao_competicao inscricao_competicao_cd_competicao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.inscricao_competicao
    ADD CONSTRAINT inscricao_competicao_cd_competicao_fkey FOREIGN KEY (cd_competicao) REFERENCES public.competicao(cd_competicao) ON DELETE CASCADE;


--
-- Name: origem_participante_confronto origem_participante_confronto_cd_classificacao_grupo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.origem_participante_confronto
    ADD CONSTRAINT origem_participante_confronto_cd_classificacao_grupo_fkey FOREIGN KEY (cd_classificacao_grupo) REFERENCES public.classificacao_grupo(cd_classificacao_grupo) ON DELETE CASCADE;


--
-- Name: origem_participante_confronto origem_participante_confronto_cd_confronto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.origem_participante_confronto
    ADD CONSTRAINT origem_participante_confronto_cd_confronto_fkey FOREIGN KEY (cd_confronto) REFERENCES public.confronto(cd_confronto) ON DELETE CASCADE;


--
-- Name: origem_participante_confronto origem_participante_confronto_cd_time_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.origem_participante_confronto
    ADD CONSTRAINT origem_participante_confronto_cd_time_fkey FOREIGN KEY (cd_time) REFERENCES public."time"(cd_time) ON DELETE CASCADE;


--
-- Name: partida partida_cd_competicao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.partida
    ADD CONSTRAINT partida_cd_competicao_fkey FOREIGN KEY (cd_competicao) REFERENCES public.competicao(cd_competicao) ON DELETE CASCADE;


--
-- Name: partida partida_cd_confronto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.partida
    ADD CONSTRAINT partida_cd_confronto_fkey FOREIGN KEY (cd_confronto) REFERENCES public.confronto(cd_confronto) ON DELETE SET NULL;


--
-- Name: partida partida_cd_rodada_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.partida
    ADD CONSTRAINT partida_cd_rodada_fkey FOREIGN KEY (cd_rodada) REFERENCES public.rodada_competicao(cd_rodada) ON DELETE SET NULL;


--
-- Name: penalidade penalidade_cd_competicao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.penalidade
    ADD CONSTRAINT penalidade_cd_competicao_fkey FOREIGN KEY (cd_competicao) REFERENCES public.competicao(cd_competicao) ON DELETE CASCADE;


--
-- Name: penalidade penalidade_cd_partida_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.penalidade
    ADD CONSTRAINT penalidade_cd_partida_fkey FOREIGN KEY (cd_partida) REFERENCES public.partida(cd_partida) ON DELETE CASCADE;


--
-- Name: penalidade penalidade_cd_tipo_penalidade_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.penalidade
    ADD CONSTRAINT penalidade_cd_tipo_penalidade_fkey FOREIGN KEY (cd_tipo_penalidade) REFERENCES public.tipo_penalidade(cd_tipo_penalidade) ON DELETE RESTRICT;


--
-- Name: penalidade penalidade_cd_vinculo_time_integrante_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.penalidade
    ADD CONSTRAINT penalidade_cd_vinculo_time_integrante_fkey FOREIGN KEY (cd_vinculo_time_integrante) REFERENCES public.vinculo_time_integrante(cd_vinculo_time_integrante) ON DELETE CASCADE;


--
-- Name: responsavel responsavel_cd_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.responsavel
    ADD CONSTRAINT responsavel_cd_usuario_fkey FOREIGN KEY (cd_usuario) REFERENCES public.usuario(cd_usuario) ON DELETE CASCADE;


--
-- Name: resultado_etapa_partida resultado_etapa_partida_cd_etapa_partida_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.resultado_etapa_partida
    ADD CONSTRAINT resultado_etapa_partida_cd_etapa_partida_fkey FOREIGN KEY (cd_etapa_partida) REFERENCES public.etapa_partida(cd_etapa_partida) ON DELETE CASCADE;


--
-- Name: resultado_etapa_partida resultado_etapa_partida_cd_time_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.resultado_etapa_partida
    ADD CONSTRAINT resultado_etapa_partida_cd_time_fkey FOREIGN KEY (cd_time) REFERENCES public."time"(cd_time) ON DELETE CASCADE;


--
-- Name: rodada_competicao rodada_competicao_cd_etapa_competicao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.rodada_competicao
    ADD CONSTRAINT rodada_competicao_cd_etapa_competicao_fkey FOREIGN KEY (cd_etapa_competicao) REFERENCES public.etapa_competicao(cd_etapa_competicao) ON DELETE CASCADE;


--
-- Name: time time_cd_esporte_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public."time"
    ADD CONSTRAINT time_cd_esporte_fkey FOREIGN KEY (cd_esporte) REFERENCES public.esporte(cd_esporte) ON DELETE RESTRICT;


--
-- Name: usuario_papel usuario_papel_cd_papel_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.usuario_papel
    ADD CONSTRAINT usuario_papel_cd_papel_fkey FOREIGN KEY (cd_papel) REFERENCES public.papel(cd_papel) ON DELETE CASCADE;


--
-- Name: usuario_papel usuario_papel_cd_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.usuario_papel
    ADD CONSTRAINT usuario_papel_cd_usuario_fkey FOREIGN KEY (cd_usuario) REFERENCES public.usuario(cd_usuario) ON DELETE CASCADE;


--
-- Name: vinculo_grupo_time vinculo_grupo_time_cd_grupo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_grupo_time
    ADD CONSTRAINT vinculo_grupo_time_cd_grupo_fkey FOREIGN KEY (cd_grupo) REFERENCES public.grupo_competicao(cd_grupo) ON DELETE CASCADE;


--
-- Name: vinculo_grupo_time vinculo_grupo_time_cd_time_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_grupo_time
    ADD CONSTRAINT vinculo_grupo_time_cd_time_fkey FOREIGN KEY (cd_time) REFERENCES public."time"(cd_time) ON DELETE CASCADE;


--
-- Name: vinculo_inscricao_time vinculo_inscricao_time_cd_inscricao_competicao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_inscricao_time
    ADD CONSTRAINT vinculo_inscricao_time_cd_inscricao_competicao_fkey FOREIGN KEY (cd_inscricao_competicao) REFERENCES public.inscricao_competicao(cd_inscricao_competicao) ON DELETE CASCADE;


--
-- Name: vinculo_inscricao_time vinculo_inscricao_time_cd_time_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_inscricao_time
    ADD CONSTRAINT vinculo_inscricao_time_cd_time_fkey FOREIGN KEY (cd_time) REFERENCES public."time"(cd_time) ON DELETE CASCADE;


--
-- Name: vinculo_partida_time vinculo_partida_time_cd_partida_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_partida_time
    ADD CONSTRAINT vinculo_partida_time_cd_partida_fkey FOREIGN KEY (cd_partida) REFERENCES public.partida(cd_partida) ON DELETE CASCADE;


--
-- Name: vinculo_partida_time vinculo_partida_time_cd_time_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_partida_time
    ADD CONSTRAINT vinculo_partida_time_cd_time_fkey FOREIGN KEY (cd_time) REFERENCES public."time"(cd_time) ON DELETE CASCADE;


--
-- Name: vinculo_tecnico_time vinculo_tecnico_time_cd_time_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_tecnico_time
    ADD CONSTRAINT vinculo_tecnico_time_cd_time_fkey FOREIGN KEY (cd_time) REFERENCES public."time"(cd_time) ON DELETE CASCADE;


--
-- Name: vinculo_tecnico_time vinculo_tecnico_time_cd_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_tecnico_time
    ADD CONSTRAINT vinculo_tecnico_time_cd_usuario_fkey FOREIGN KEY (cd_usuario) REFERENCES public.usuario(cd_usuario) ON DELETE CASCADE;


--
-- Name: vinculo_time_escola vinculo_time_escola_cd_escola_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_time_escola
    ADD CONSTRAINT vinculo_time_escola_cd_escola_fkey FOREIGN KEY (cd_escola) REFERENCES public.escola(cd_escola) ON DELETE CASCADE;


--
-- Name: vinculo_time_escola vinculo_time_escola_cd_time_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_time_escola
    ADD CONSTRAINT vinculo_time_escola_cd_time_fkey FOREIGN KEY (cd_time) REFERENCES public."time"(cd_time) ON DELETE CASCADE;


--
-- Name: vinculo_time_integrante vinculo_time_integrante_cd_funcao_integrante_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_time_integrante
    ADD CONSTRAINT vinculo_time_integrante_cd_funcao_integrante_fkey FOREIGN KEY (cd_funcao_integrante) REFERENCES public.funcao_integrante(cd_funcao_integrante) ON DELETE SET NULL;


--
-- Name: vinculo_time_integrante vinculo_time_integrante_cd_time_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_time_integrante
    ADD CONSTRAINT vinculo_time_integrante_cd_time_fkey FOREIGN KEY (cd_time) REFERENCES public."time"(cd_time) ON DELETE CASCADE;


--
-- Name: vinculo_time_integrante vinculo_time_integrante_cd_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_time_integrante
    ADD CONSTRAINT vinculo_time_integrante_cd_usuario_fkey FOREIGN KEY (cd_usuario) REFERENCES public.usuario(cd_usuario) ON DELETE CASCADE;


--
-- Name: vinculo_time_responsavel vinculo_time_responsavel_cd_responsavel_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_time_responsavel
    ADD CONSTRAINT vinculo_time_responsavel_cd_responsavel_fkey FOREIGN KEY (cd_responsavel) REFERENCES public.responsavel(cd_responsavel) ON DELETE CASCADE;


--
-- Name: vinculo_time_responsavel vinculo_time_responsavel_cd_time_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_time_responsavel
    ADD CONSTRAINT vinculo_time_responsavel_cd_time_fkey FOREIGN KEY (cd_time) REFERENCES public."time"(cd_time) ON DELETE CASCADE;


--
-- Name: vinculo_usuario_escola vinculo_usuario_escola_cd_escola_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_usuario_escola
    ADD CONSTRAINT vinculo_usuario_escola_cd_escola_fkey FOREIGN KEY (cd_escola) REFERENCES public.escola(cd_escola) ON DELETE CASCADE;


--
-- Name: vinculo_usuario_escola vinculo_usuario_escola_cd_papel_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_usuario_escola
    ADD CONSTRAINT vinculo_usuario_escola_cd_papel_fkey FOREIGN KEY (cd_papel) REFERENCES public.papel(cd_papel) ON DELETE RESTRICT;


--
-- Name: vinculo_usuario_escola vinculo_usuario_escola_cd_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ace
--

ALTER TABLE ONLY public.vinculo_usuario_escola
    ADD CONSTRAINT vinculo_usuario_escola_cd_usuario_fkey FOREIGN KEY (cd_usuario) REFERENCES public.usuario(cd_usuario) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict yO5qPpQtLWf9arJgMtrckG2RbkX06tQMcoNu1RNSxuCozDPY3QcxHNnOdumJ8sY

